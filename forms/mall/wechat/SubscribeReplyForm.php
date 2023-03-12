<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/26
 * Time: 9:21 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\core\response\ApiCode;
use app\forms\common\wechat\WechatFactory;
use app\models\Model;
use app\models\WechatSubscribeReply;

class SubscribeReplyForm extends Model
{
    public $type;
    public $content;
    public $title;
    public $picurl;
    public $url;

    public function rules()
    {
        return [
            [['type'], 'integer'],
            ['type', 'in', 'range' => [0, 1, 2, 3, 4]],
            [['title', 'content', 'picurl', 'url'], 'trim'],
            [['title', 'content', 'picurl', 'url'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '消息类型',
            'content' => '消息内容',
            'title' => '图文消息标题',
            'picurl' => '图文消息图片链接',
            'url' => '链接',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $model = WechatSubscribeReply::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 0]);
            if (!$model) {
                $model = new WechatSubscribeReply();
                $model->mall_id = \Yii::$app->mall->id;
                $model->is_delete = 0;
                $model->status = 0;
            }
            $model->type = $this->type;
            $message = WechatFactory::createMessage($this->type);
            $message->attributes = $this->attributes;
            if (!$message->validate()) {
                throw new \Exception($this->getErrorMsg($message));
            }
            $model->media_id = $message->getMedia($model);
            $model->attributes = $this->attributes;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'errors' => $exception
            ];
        }
    }

    public function getDetail()
    {
        $model = WechatSubscribeReply::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 0]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'type' => 0,
                    'title' => '',
                    'content' => '',
                    'url' => '',
                    'picurl' => '',
                ]
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'type' => $model->type,
                'title' => $model->title,
                'content' => $model->content,
                'url' => $model->url,
                'picurl' => $model->picurl,
            ]
        ];
    }
}
