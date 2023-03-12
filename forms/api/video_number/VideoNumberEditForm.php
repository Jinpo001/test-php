<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\video_number;

use Curl\Curl;
use app\core\response\ApiCode;
use app\forms\api\video_number\CommonStyle;
use app\forms\api\video_number\CommonUpload;
use app\forms\common\mptemplate\MpTplGet;
use app\forms\common\wechat\WechatFactory;
use app\forms\mall\theme_color\ThemeColorForm;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\UserIdentity;
use app\models\VideoNumber;
use app\models\VideoNumberData;
use app\plugins\wxapp\models\WxappConfig;
use app\plugins\wxapp\models\WxappWxminiprograms;

class VideoNumberEditForm extends Model
{
    public $goods_id;
    public $media_id;

    private $accessToken;
    private $goodsSetting;
    private $videoNumberSetting;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
            [['media_id'], 'string'],
        ];
    }

    public function save()
    {
        try {
            $this->videoNumberSetting = $this->getVideoNumber();
            $this->accessToken = WechatFactory::create('video')->wechat->getAccessToken();
            $this->checkVideoNumber();
            $this->goodsSetting = $this->getGoods();

            $videoNumber = VideoNumber::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'goods_id' => $this->goods_id
            ])->one();

            $isDebug = env('VIDEO_NUMBER_DEBUG') ? true : false;

            if ($videoNumber && !$isDebug) {
                $res['media_id'] = $videoNumber->media_id;
            } else {
                $api = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$this->accessToken}";

                $content = (new CommonStyle())->getStyle($this->videoNumberSetting['video_number_template_list'][0]['id'], $this->goodsSetting);

                $postData = [
                    "articles"=> [
                        [
                            "title"=> $this->goodsSetting['app_share_title'],
                            "thumb_media_id"=> $this->goodsSetting['app_share_pic'],
                            "author"=> \Yii::$app->user->identity->nickname,
                            "show_cover_pic"=> 0,
                            "content"=> $content,
                            "content_source_url"=> '',
                            "need_open_comment"=>0,
                            "only_fans_can_comment"=>0
                        ]
                    ]
                ];

                $res = CommonVideoNumber::post($api, $postData);
                $res = json_decode($res->getBody()->getContents(), true);

                if (!isset($res['media_id'])) {
                    \Yii::error($res);
                    $this->handelMessage($res);
                }

                $videoNumber = new VideoNumber();
                $videoNumber->mall_id = \Yii::$app->mall->id;
                $videoNumber->media_id = $res['media_id'];
                $videoNumber->user_id = \Yii::$app->user->id;
                $videoNumber->goods_id = $this->goods_id;

                $extraAttributes['save_result'] = $res;

                // 发送
                $sendData = $this->send($res['media_id']);

                $extraAttributes['send_result'] = $sendData;
                $videoNumber->extra_attributes = json_encode($extraAttributes);
                $videoNumber->msg_id = (string)$sendData['msg_id'];
                $videoNumber->save();
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'media_id' => $res['media_id'],
                ]
            ];

        }catch(\Exception $exception) {
            \Yii::warning('视频号操作异常');
            \Yii::warning($exception);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    // 当微信图片media_id失效时(素材被删除) 需将数据库记录删除 以便重新生成新的media_id
    private function deleteImageMediaIdLog($mediaId)
    {
        $videoNumberData = VideoNumberData::find()->andWhere(['value' => $mediaId])->one();
        if ($videoNumberData) {
            $videoNumberData->delete();
        }
    }

    private function checkVideoNumber()
    {
        $setting = $this->videoNumberSetting;
        if (!$setting['is_video_number']) {
            throw new \Exception("视频号功能未开启");
        }

//        if (!$setting['video_number_app_id'] || !$setting['video_number_app_secret']) {
//            throw new \Exception('视频号appId或appSecret未配置');
//        }

        if ($setting['is_video_number_member']) {
            $userIdentity = UserIdentity::find()->andWhere(['user_id' => \Yii::$app->user->id])->select('member_level')->one();

            if (count($setting['video_number_member_list'])) {
                $levelList = [];
                foreach ($setting['video_number_member_list'] as $item) {
                    $levelList[] = $item['level'];
                }

                if (!in_array($userIdentity->member_level, $levelList)) {
                    throw new \Exception("您的会员等级无操作权限");
                }
            }
        }

        if (!count($setting['video_number_template_list'])) {
            throw new \Exception("未选择模板");
        }
    }

    // 素材群发
    private function send($mediaId)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$this->accessToken}";

        $postData= [
           "touser"=>[
              $this->videoNumberSetting['video_number_user_1'],
              $this->videoNumberSetting['video_number_user_2']
           ],
           "mpnews"=>[
              "media_id"=> $mediaId
           ],
            "msgtype"=>"mpnews",
            "send_ignore_reprint"=>0,
        ];

        $res = CommonVideoNumber::post($api, $postData);
        $res = json_decode($res->getBody()->getContents(), true);

        if (!isset($res['msg_id'])) {
            \Yii::error($res);
            $this->handelMessage($res);
        }

        return $res;
    }

    private function handelMessage($res)
    {
        if ($res['errcode'] == 40031) {
            throw new \Exception('请检查openId是否正确');
        }

        if ($res['errcode'] == 40007) {
            $this->deleteImageMediaIdLog($this->goodsSetting['app_share_pic']);
            throw new \Exception('请重试');
        }

        if ($res['errcode'] == 40192) {
            throw new \Exception('微信昵称包含违禁字，生成链接失败！');
        }

        if ($res['errcode'] == -1) {
            throw new \Exception('系统繁忙！请稍后再试');
        }

        throw new \Exception(json_encode($res));
    }

    private function getVideoNumber()
    {
        $mall = new Mall();
        $data = $mall->getMallSetting([
            'is_video_number',
            'video_number_app_id',
            'video_number_app_secret',
            'is_video_number_member',
            'video_number_member_list',
            'video_number_template_list',
            'video_number_share_title',
            'video_number_user_1',
            'video_number_user_2',
            'mall_logo_pic'
        ]);

        return $data;
    }

    private function getGoods()
    {
        $goods = Goods::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->goods_id
        ])->with('goodsWarehouse')->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }

        if (!$goods->goodsWarehouse->cover_pic) {
            throw new \Exception('商品未设置封面图');
        }

        $baseUrl = \Yii::$app->request->hostInfo . '/' . \Yii::$app->request->baseUrl;
        $shopLocalUrl = $baseUrl . '/statics/img/mall/sph/shop.png';
        $cartLocalUrl = $baseUrl . '/statics/img/mall/sph/cart.png';

        $themeList = (new ThemeColorForm())->getThemeData();
        $themeColor = '';
        foreach ($themeList as $item) {
            if ($item['is_select']) {
                $rgbArray = hex2rgb($item['color']['main']);
                $themeColor = $rgbArray['r'] . ',' . $rgbArray['g'] . ',' . $rgbArray['b'];
                break;
            }
        }

        $newPicList = [];
        if ($this->videoNumberSetting['video_number_template_list'][0]['id'] == 4) {
            $picList = json_decode($goods->goodsWarehouse->pic_url, true);
            foreach ($picList as $item) {
                $newPicList[] = $this->getImageUrl('goods_url', $item['pic_url']);
            }
        }

        $wxappConfig = WxappConfig::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
        $appId = $wxappConfig ? $wxappConfig->appid : '';

        $wxMini = WxappWxminiprograms::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->one();
        $appId = $wxMini ? $wxMini->authorizer_appid : $appId;

        return [
            'app_share_title' => $goods->app_share_title ?: $this->videoNumberSetting['video_number_share_title'],
            'app_share_pic' => $this->getImageMediaId('goods_media_id', $goods->app_share_pic ?: $goods->goodsWarehouse->cover_pic),
            'name' => $goods->goodsWarehouse->name,
            'subtitle' => $goods->goodsWarehouse->subtitle,
            'keyword' => $goods->goodsWarehouse->keyword,
            'original_price' => $goods->goodsWarehouse->original_price,
            'price' => $goods->price,
            'cover_pic' => $this->getImageUrl('goods_url', $goods->goodsWarehouse->cover_pic),
            'page_url' => $this->getPageUrl($goods),
            'app_id' => $appId,
            'shop_icon' => $this->getImageUrl('shop_icon_url', $this->videoNumberSetting['mall_logo_pic'] ?: $shopLocalUrl),
            'theme_color' => $themeColor,
            'pic_list' => $newPicList,
            'cart_icon' => $this->getImageUrl('cart_icon_url', $cartLocalUrl)
        ];
    }

    private function getPageUrl($goods)
    {
        try {
            $sign = $goods->sign ?: 'wxapp';
            if ($goods->mch_id > 0) {
                $sign = 'mch';
            }
            $plugins = \Yii::$app->plugin->getPlugin($sign);
            if (is_callable(array($plugins, 'getGoodsUrl'))) {
                $pageUrl = $plugins->getGoodsUrl($goods);
                $pageUrl = $pageUrl . '&user_id=' . \Yii::$app->user->id;
            } else {
                $pageUrl = '';
            }

        } catch (\Exception $exception) {
            $pageUrl = '';
        }

        return $pageUrl;
    }

    private function getImageMediaId($type, $picUrl)
    {
        $md5 = md5($picUrl);
        $setting = VideoNumberData::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'key' => $md5,
            'type' => $type,
        ])->one();

        if (!$setting) {
            $mediaId = (new CommonUpload())->uploadImage($this->accessToken, $picUrl);

            $setting = new VideoNumberData();
            $setting->mall_id = \Yii::$app->mall->id;
            $setting->type = $type;
            $setting->key = $md5;
            $setting->value =$mediaId;
            $setting->save();
        }

        return $setting->value;
    }


    private function getImageUrl($type, $picUrl)
    {
        $md5 = md5($picUrl);
        $setting = VideoNumberData::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'key' => $md5,
            'type' => $type,
        ])->one();

        if (!$setting) {
            $returnUrl = (new CommonUpload())->uploadImageReturnUrl($this->accessToken, $picUrl);

            $setting = new VideoNumberData();
            $setting->mall_id = \Yii::$app->mall->id;
            $setting->type = $type;
            $setting->key = $md5;
            $setting->url =$returnUrl;
            $setting->save();

        }

        return $setting->url;
    }

    public function getArticleUrl()
    {
        try {
            $videoNumber = VideoNumber::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'media_id' => $this->media_id,
            ])->one();

            if (!$videoNumber) {
                throw new \Exception("记录不存在");
            }

            // 当素材超过3分钟没有收到回调url 需删除记录重新生成
            $isTrue = (time() - strtotime($videoNumber->created_at)) > 180;
            if ($videoNumber->status == '' && $isTrue) {
                $videoNumber->delete();
                throw new \Exception('请重试');
            }

            $extraAttributes = json_decode($videoNumber->extra_attributes, true);
            $url = '';

            if (isset($extraAttributes['event_result']['ArticleUrlResult']['ResultList']['item']['ArticleUrl'])) {
                $url = $extraAttributes['event_result']['ArticleUrlResult']['ResultList']['item']['ArticleUrl'];

                if(strpos($url,'https') === false){ 
                    $url = str_replace("http","https",$url);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'url' => $url
                ]
            ];

        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
