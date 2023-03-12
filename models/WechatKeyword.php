<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%wechat_keyword}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $rule_id 规则id
 * @property string $name 关键词
 * @property int $status 匹配方式0--全匹配1--模糊匹配
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class WechatKeyword extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_keyword}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'rule_id', 'status', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'rule_id' => '规则id',
            'name' => '关键词',
            'status' => '匹配方式0--全匹配1--模糊匹配',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getStatusText()
    {
        $text = ['全匹配', '模糊匹配'];
        return $text[$this->status];
    }
}
