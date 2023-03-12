<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_params_template}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 模板名称
 * @property string $content 参数内容
 * @property string $select_data 搜索使用
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 */
class GoodsParamsTemplate extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_params_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id', 'mch_id', 'is_delete'], 'integer'],
            [['content', 'select_data'], 'string'],
            [['created_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 100],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'name' => '模板名称',
            'content' => '参数内容',
            'select_data' => '搜索使用',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
