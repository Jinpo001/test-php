<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\goods\params;


use app\core\response\ApiCode;
use app\models\GoodsAttrTemplate;
use app\models\GoodsParamsTemplate;
use app\models\Model;

class GoodsParamsTemplateForm extends Model
{
    public $page;
    public $id;
    public $name;
    public $content;

    public $page_size;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'page', 'page_size'], 'integer'],
            [['content'], 'trim'],
            [['name', 'keyword'], 'string', 'max' => 100],
            [['page_size'], 'default', 'value' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'name' => '模板名称',
            'content' => '参数内容',
        ];
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = GoodsParamsTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ]);
        if ($this->keyword) {
            $regexp = $this->keyword;
            $concat = sprintf('.*%s.*[[:space:]]', $regexp);
            $query->andWhere([
                'OR',
                ['like', 'name', $this->keyword],
                ['REGEXP', 'select_data', $concat]
            ]);
        }

        $template = $query->page($pagination, $this->page_size)->all();
        $template = array_map(function ($item) {
            $item['content'] = \yii\helpers\BaseJson::decode($item['content']);
            return $item;
        }, $template);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $template,
                'pagination' => $pagination
            ]
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = GoodsParamsTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
        ])->one();
        if (empty($model)) {
            $model = new GoodsParamsTemplate();
            $model->mch_id = \Yii::$app->user->identity->mch_id;
            $model->mall_id = \Yii::$app->mall->id;
        }

        $model->attributes = $this->attributes;
        $model->content = \yii\helpers\BaseJson::encode($this->content);
        $arr = array_merge_recursive(array_column($this->content, 'label'), array_column($this->content, 'value'));
        $model->select_data = join("\r", $arr) . "\r";
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }

    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $model = GoodsParamsTemplate::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ])->one();
        if (empty($model)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据已清空'
            ];
        }
        $model->is_delete = 1;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }
}