<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/2
 * Time: 2:38 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\WechatKeyword;
use app\models\WechatKeywordRules;
use app\models\WechatSubscribeReply;

class KeywordRuleForm extends Model
{
    public $id;
    public $name;
    public $status;
    public $reply_list;
    public $keyword_list;

    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 15],
            [['status'], 'in', 'range' => [0, 1]],
            [['reply_list', 'keyword_list'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '规则名称',
            'status' => '回复方式',
            'keyword_list' => '关键词',
            'reply_list' => '回复内容'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (empty($this->keyword_list)) {
                throw new \Exception('关键词不能为空');
            }
            if (empty($this->reply_list)) {
                throw new \Exception('回复内容不能为空');
            }
            $model = WechatKeywordRules::findOne([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ]);
            if (!$model) {
                $model = new WechatKeywordRules();
                $model->mall_id = \Yii::$app->mall->id;
                $model->is_delete = 0;
            }
            $model->name = $this->name;
            $model->reply_id = implode(',', $this->reply_list);
            $model->status = $this->status;
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            WechatKeyword::updateAll(['is_delete' => 1], ['rule_id' => $model->id]);
            foreach ($this->keyword_list as $item) {
                $keywordForm = new KeywordForm();
                $keywordForm->attributes = $item;
                $keywordForm->rule_id = $model->id;
                $keywordForm->save();
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getDetail()
    {
        $model = WechatKeywordRules::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => []
            ];
        }
        $list = $model->toArray();
        $list['keyword_list'] = [];
        $list['reply_list'] = [];
        $list['show_reply_list'] = [];
        foreach ($model->keyword as $keyword) {
            $list['keyword_list'][] = [
                'name' => $keyword->name,
                'status' => $keyword->status
            ];
        }
        $replyList = WechatSubscribeReply::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0, 'status' => 1, 'id' => explode(',', $model->reply_id)
        ])->all();
        /* @var WechatSubscribeReply[] $replyList */
        foreach ($replyList as $record) {
            $list['reply_list'][] = $record->id;
            $list['show_reply_list'][] = [
                'id' => $record->id,
                'type' => $record->type,
                'content' => $record->content,
                'url' => $record->url,
                'picurl' => $record->picurl,
                'title' => $record->title,
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $list
        ];
    }
}
