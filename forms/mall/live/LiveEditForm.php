<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\forms\mall\live\CommonUpload;
use app\models\Model;

class LiveEditForm extends Model
{
    public $name;
    public $cover_img;
    public $start_time;
    public $end_time;
    public $anchor_name;
    public $anchor_wechat;
    public $share_img;
    public $feedsImg;
    public $type;
    public $screen_type;
    public $close_like;
    public $close_goods;
    public $close_comment;

    public $is_feeds_public;
    public $close_replay;
    public $close_share;
    public $close_kf;

    public function rules()
    {
        return [
            [['name', 'cover_img', 'start_time', 'end_time', 'anchor_name', 'anchor_wechat', 'share_img', 'type', 'screen_type', 'close_like', 'close_goods', 'close_comment', 'feedsImg'], 'required'],
            [['name', 'cover_img', 'start_time', 'end_time', 'anchor_name', 'anchor_wechat', 'share_img', 'feedsImg'], 'string'],
            [['type', 'screen_type', 'close_like', 'close_goods', 'close_comment','is_feeds_public', 'close_replay', 'close_share', 'close_kf'], 'integer'],
            [['name', 'anchor_name', 'anchor_wechat'], 'trim'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '直播间名称',
            'cover_img' => '直播背景图',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'anchor_name' => '主播昵称',
            'anchor_wechat' => '主播微信号',
            'share_img' => '直播分享图',
            'feedsImg' => '频道封面图',
            'type' => '直播间类型',
            'screen_type' => '横屏、竖屏',
            'close_like' => '是否关闭点赞',
            'close_goods' => '是否关闭货架',
            'close_comment' => '是否关闭评论',
            'is_feeds_public' => '是否开启官方收录',
            'close_replay' => '是否开启回放',
            'close_share' => '是否开启分享',
            'close_kf' => '是否开启客服',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();
            $accessToken = CommonLive::checkAccessToken();
            $coverImgId = (new CommonUpload())->uploadImage($accessToken, $this->cover_img, 2, [], [], '直播背景图');
            $shareImgId = (new CommonUpload())->uploadImage($accessToken, $this->share_img, 1, [], [], '直播分享图');
            $feedsImg = (new CommonUpload())->uploadImage($accessToken, $this->feedsImg, '100KB', [], [], '频道封面图');
            // 接口每天上限调用10000次
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token={$accessToken}";
            $res = CommonLive::post($api, [
                'name' => $this->name,
                'coverImg' => $coverImgId,
                'startTime' => strtotime($this->start_time),
                'endTime' => strtotime($this->end_time),
                'anchorName' => $this->anchor_name,
                'anchorWechat' => $this->anchor_wechat,
                'shareImg' => $shareImgId,
                'type' => $this->type,
                'screenType' => $this->screen_type,
                'closeLike' => $this->close_like,
                'closeGoods' => $this->close_goods,
                'closeComment' => $this->close_comment,
                'isFeedsPublic' => $this->is_feeds_public,
                'closeReplay' => $this->close_replay,
                'closeShare' => $this->close_share,
                'closeKf' => $this->close_kf,
                'feedsImg' => $feedsImg
            ]);
            $res = json_decode($res->getBody()->getContents(), true);

            if ($res['errcode'] != 0) {
                $this->updateErrorMsg($res);
                throw new \Exception($res['errmsg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "添加成功",
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    private function updateErrorMsg($res)
    {
        if (strstr($res['errmsg'], 'anchorWechat')) {
            throw new \Exception('无效的微信号');
        }

        if (strstr($res['errmsg'], 'startTime') || strstr($res['errmsg'], 'endTime')) {
            throw new \Exception('请输入有效的直播时间');
        }
    }

    private function checkData()
    {
        if (strlen($this->name) > 17 * 3 || strlen($this->name) < 3 * 3) {
            throw new \Exception('直播间名称最短3个汉字，最大17个汉字');
        }
        if (strlen($this->anchor_name) > 15 * 3 || strlen($this->anchor_name) < 2 * 3) {
            throw new \Exception('主播昵称最短2个汉字，最大15个汉字');
        }

        if (strtotime($this->start_time) <= (time() + 600) || strtotime($this->start_time) > (time() + 6 * 30 * 24 * 60 * 60)) {
            throw new \Exception("开播时间需要在当前时间的10分钟后 并且 开始时间不能在 6 个月后");
        }
        if (strtotime($this->end_time) - strtotime($this->start_time) < (30 * 60) || strtotime($this->end_time) - strtotime($this->start_time) > (24 * 60 * 60)) {
            throw new \Exception("开播时间和结束时间间隔不得短于30分钟，不得超过24小时");
        }
    }
}
