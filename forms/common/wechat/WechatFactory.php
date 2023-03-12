<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/24
 * Time: 10:20 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat;

use app\forms\common\CommonOption;
use app\forms\common\wechat\config\ThirdWechat;
use app\forms\common\wechat\config\Wechat;
use app\forms\common\wechat\message\ImageMessage;
use app\forms\common\wechat\message\NewsMessage;
use app\forms\common\wechat\message\VideoMessage;
use app\forms\common\wechat\message\VoiceMessage;
use app\forms\common\wechat\message\WordMessage;
use app\forms\common\wechat\reply\KeywordReply;
use app\forms\common\wechat\service\CustomService;
use app\forms\common\wechat\service\MediaService;
use app\forms\common\wechat\service\WechatMenu;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use app\models\WechatConfig;
use app\models\WechatSubscribeReply;
use app\models\WechatWxmpprograms;

class WechatFactory extends Model
{
    /**
     * @var \luweiss\Wechat\Wechat $wechat
     */
    public $wechat;

    public $accessToken;

    /**
     * @var MediaService $mediaService
     */
    public $mediaService;

    /**
     * @var CustomService $customService
     */
    public $customService;

    /**
     * @var WechatMenu $menuService
     */
    public $menuService;

    /**
     * @var Mall $mall
     */
    public $mall;

    /**
     * @param string $type wechat_plugin--公众号插件|template--消息发送|video--视频号
     * @param null $mall
     * @return WechatFactory
     */
    public static function create($type = 'wechat_plugin', $mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $instance = new self();
        $instance->mall = $mall;
        $wechat = $instance->newConfig();
        if (!$wechat) {
            switch ($type) {
                case 'wechat_plugin':
                    $wechat = $instance->wechatPlugin();
                    break;
                case 'template':
                    $wechat = $instance->template();
                    break;
                case 'video':
                    $wechat = $instance->video();
                    break;
                default:
                    throw new \Exception('错误的类型');
            }
        }
        $instance->wechat = $wechat;
        $instance->accessToken = $wechat->getAccessToken();
        $instance->mediaService = $instance->getMediaService();
        $instance->customService = $instance->getCustomService();
        $instance->menuService = $instance->getMenuService();
        return $instance;
    }

    /**
     * @param $authorizerAppid
     * @return WechatWxmpprograms|null
     * 获取第一版和第二版兼容数据
     */
    public static function getThird($authorizerAppid)
    {
        $third = WechatWxmpprograms::find()->where(['authorizer_appid' => $authorizerAppid, 'version' => 2])->exists();
        // 如果第二版数据为设置，则获取第一版的数据
        if (!$third) {
            $third = WechatWxmpprograms::findOne([
                'authorizer_appid' => $authorizerAppid, 'is_delete' => 0, 'version' => 1
            ]);
        } else {
            // 如果第二版的数据已设置，但是被删除了，则返回null
            $third = WechatWxmpprograms::findOne([
                'authorizer_appid' => $authorizerAppid, 'version' => 2, 'is_delete' => 0
            ]);
        }
        return $third;
    }

    /**
     * @param $mallId
     * @return WechatWxmpprograms|null
     * 获取第一版和第二版兼容数据 通过商城id
     */
    public static function getThirdByMall($mallId)
    {
        $third = WechatWxmpprograms::find()->where(['mall_id' => $mallId, 'version' => 2])->exists();
        // 如果第二版数据为设置，则获取第一版的数据
        if (!$third) {
            $third = WechatWxmpprograms::findOne([
                'mall_id' => $mallId, 'is_delete' => 0, 'version' => 1
            ]);
        } else {
            // 如果第二版的数据已设置，但是被删除了，则返回null
            $third = WechatWxmpprograms::findOne(['mall_id' => $mallId, 'version' => 2, 'is_delete' => 0]);
        }
        return $third;
    }

    /**
     * @param $mallId
     * @return WechatConfig|null
     * 通过商城id获取第一版和第二版的兼容数据
     */
    public static function getConfigByMallId($mallId)
    {
        $model = WechatConfig::findOne(['mall_id' => $mallId, 'version' => 2]);
        // 如果第二版数据为设置，则获取第一版的数据
        if (!$model) {
            $model = WechatConfig::findOne([
                'mall_id' => $mallId, 'is_delete' => 0, 'version' => 1
            ]);
        } else {
            // 如果第二版的数据已设置，但是被删除了，则返回null
            if ($model->is_delete == 1) {
                $model = null;
            }
        }
        return $model;
    }

    protected function newConfig()
    {
        $third = WechatWxmpprograms::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'version' => 2]);
        if ($third) {
            return new ThirdWechat([
                'appId' => $third->authorizer_appid,
                'cache' => [
                    'target' => Wechat::CACHE_TARGET_FILE,
                    'dir' => \Yii::$app->runtimePath . '/wechat-cache',
                ],
            ]);
        }
        $wxappConfig = WechatConfig::findOne(['mall_id' => $this->mall->id, 'version' => 2]);
        if (!$wxappConfig || !$wxappConfig->appid || !$wxappConfig->appsecret) {
            return null;
        }
        return new Wechat([
            'appId' => $wxappConfig->appid,
            'appSecret' => $wxappConfig->appsecret,
            'cache' => [
                'target' => Wechat::CACHE_TARGET_FILE,
                'dir' => \Yii::$app->runtimePath . '/wechat-cache',
            ],
            'name' => $wxappConfig->name,
            'logo' => $wxappConfig->logo,
            'qrcode' => $wxappConfig->qrcode,
        ]);
    }

    /**
     * @return ThirdWechat|Wechat
     * @throws \luweiss\Wechat\WechatException
     * 旧版数据
     */
    protected function wechatPlugin()
    {
        $third = WechatWxmpprograms::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'version' => 1]);
        if ($third) {
            return new ThirdWechat([
                'appId' => $third->authorizer_appid,
                'cache' => [
                    'target' => Wechat::CACHE_TARGET_FILE,
                    'dir' => \Yii::$app->runtimePath . '/wechat-cache',
                ],
            ]);
        }
        $wxappConfig = WechatConfig::findOne(['mall_id' => $this->mall->id, 'version' => 1]);
        if (!$wxappConfig || !$wxappConfig->appid || !$wxappConfig->appsecret) {
            throw new \Exception('微信公众平台信息尚未配置。');
        }
        return new Wechat([
            'appId' => $wxappConfig->appid,
            'appSecret' => $wxappConfig->appsecret,
            'cache' => [
                'target' => Wechat::CACHE_TARGET_FILE,
                'dir' => \Yii::$app->runtimePath . '/wechat-cache',
            ],
            'name' => $wxappConfig->name,
            'logo' => $wxappConfig->logo,
            'qrcode' => $wxappConfig->qrcode,
        ]);
    }

    /**
     * @return Wechat
     * @throws \luweiss\Wechat\WechatException
     * 旧版模板消息获取公众号配置
     */
    protected function template()
    {
        $option = CommonOption::get(Option::NAME_WX_PLATFORM, $this->mall->id, Option::GROUP_APP);
        if (
            !($option && isset($option['app_id'])
            && isset($option['app_secret']) && $option['app_id'] && $option['app_secret'])
        ) {
            throw new \Exception('微信公众平台信息尚未配置。');
        }
        return new Wechat([
            'appId' => $option['app_id'],
            'appSecret' => $option['app_secret'],
            'cache' => [
                'target' => Wechat::CACHE_TARGET_FILE,
                'dir' => \Yii::$app->runtimePath . '/wechat-cache',
            ]
        ]);
    }

    /**
     * @return Wechat
     * @throws \luweiss\Wechat\WechatException
     * 旧版视频号获取公众号配置
     */
    protected function video()
    {
        $mall = new Mall();
        $setting = $mall->getMallSetting(['video_number_app_id', 'video_number_app_secret']);
        if (!$setting || !$setting['video_number_app_secret'] || !$setting['video_number_app_id']) {
            throw new \Exception('微信公众平台信息尚未配置。');
        }
        return new Wechat([
            'appId' => $setting['video_number_app_id'],
            'appSecret' => $setting['video_number_app_secret'],
            'cache' => [
                'target' => Wechat::CACHE_TARGET_FILE,
                'dir' => \Yii::$app->runtimePath . '/wechat-cache',
            ]
        ]);
    }

    /**
     * @return bool
     * 公众号配置是否设置
     */
    public static function isSetting()
    {
        $wechatConfigNew = WechatConfig::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'version' => 2]);
        $third = WechatWxmpprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'version' => 2]);
        return $wechatConfigNew || $third;
    }

    public function refresh()
    {
        $this->accessToken = $this->wechat->getAccessToken(true);
    }

    public function getMediaService()
    {
        return new MediaService([
            'accessToken' => $this->accessToken
        ]);
    }

    public function getCustomService()
    {
        return new CustomService([
            'accessToken' => $this->accessToken
        ]);
    }

    public function getMenuService()
    {
        return new WechatMenu([
            'accessToken' => $this->accessToken
        ]);
    }

    /**
     * @param array $xmlDataArray
     * @return array|string
     * @throws \Exception
     * 关注回复
     */
    public function subscribeReply($xmlDataArray)
    {
        $model = WechatSubscribeReply::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 0]);
        if (!$model) {
            return '';
        }
        $message = WechatFactory::createMessage($model->type);
        $messageArr = [
            'ToUserName' => $xmlDataArray['FromUserName'],
            'FromUserName' => $xmlDataArray['ToUserName'],
            'CreateTime' => time(),
            'MsgType' => $message->type,
        ];
        $messageArr = array_merge($messageArr, $message->reply($model));
        return $messageArr;
    }

    /**
     * @param $type
     * @return ImageMessage|NewsMessage|VideoMessage|VoiceMessage|WordMessage
     * @throws \Exception
     * 创建消息类型处理类
     */
    public static function createMessage($type)
    {
        switch ($type) {
            case 0:
                return new WordMessage();
            case 1:
                return new ImageMessage();
            case 2:
                return new VoiceMessage();
            case 3:
                return new VideoMessage();
            case 4:
                return new NewsMessage();
            default:
                throw new \Exception('错误的消息类型');
        }
    }

    /**
     * @return array|string|null
     * 获取服务器配置
     */
    public function getServer()
    {
        return CommonOption::get('wechat_server', $this->mall->id, 'mall', null, 0);
    }

    /**
     * @param array $xmlDataArray
     * @return false|mixed
     * @throws \Exception
     * 自定义菜单回复
     */
    public function menuReply($xmlDataArray)
    {
        $model = WechatSubscribeReply::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $xmlDataArray['EventKey'],
            'status' => 2
        ]);
        if (!$model) {
            return false;
        }
        $message = self::createMessage($model->type);
        $arr = [
            'touser' => $xmlDataArray['FromUserName'],
        ];
        return $this->customService->send(array_merge($arr, $message->custom($model)));
    }

    /**
     * @param array $xmlDataArray
     * @return bool
     * 关键词回复
     */
    public function keywordReply($xmlDataArray)
    {
        $reply = new KeywordReply([
            'app' => $this,
            'mall' => $this->mall
        ]);
        return $reply->reply($xmlDataArray);
    }
}
