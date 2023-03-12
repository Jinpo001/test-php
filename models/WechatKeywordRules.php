<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%wechat_keyword_rules}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 规则名称
 * @property int $status 回复方式0--全部回复1--随机一条回复
 * @property string $reply_id 回复内容
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property WechatKeyword[] $keyword
 */
class WechatKeywordRules extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_keyword_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'status', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'reply_id'], 'string', 'max' => 255],
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
            'name' => '规则名称',
            'status' => '回复方式0--全部回复1--随机一条回复',
            'reply_id' => '回复内容',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getKeyword()
    {
        return $this->hasMany(WechatKeyword::className(), ['rule_id' => 'id'])->andWhere(['is_delete' => 0]);
    }
}
