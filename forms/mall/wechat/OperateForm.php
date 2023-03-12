<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/1
 * Time: 10:23 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\models\Model;
use app\models\WechatKeywordRules;
use app\models\WechatSubscribeReply;

class OperateForm extends Model
{
    public $operate;
    public $type;
    public $id;

    public function rules()
    {
        return [
            [['operate'], 'in', 'range' => ['delete']],
            [['type'], 'in', 'range' => ['subscribe_reply', 'keyword_reply']],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'operate' => '操作方式',
            'type' => '操作类型'
        ];
    }

    public function execute()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            switch ($this->operate) {
                case 'delete':
                    $this->delete();
                    break;
                default:
            }
            return $this->success([
                'msg' => '处理成功'
            ]);
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }

    protected function delete()
    {
        switch ($this->type) {
            case 'subscribe_reply':
                $model = WechatSubscribeReply::findOne(['mall_id' => \Yii::$app->mall->id]);
                if (!$model) {
                    return false;
                }
                $model->is_delete = 1;
                if (!$model->save()) {
                    throw new \Exception($this->getErrorMsg($model));
                }
                break;
            case 'keyword_reply':
                if (!$this->id) {
                    throw new \Exception('请选择需要删除的关键词回复规则');
                }
                $model = WechatKeywordRules::findOne([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id
                ]);
                if (!$model) {
                    throw new \Exception('选择的关键词回复规则已被删除，请重新选择');
                }
                $model->is_delete = 1;
                if (!$model->save()) {
                    throw new \Exception($this->getErrorMsg($model));
                }
                break;
            default:
        }
        return true;
    }
}
