<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_remind}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $user_id
 * @property int $is_remind 是否提醒
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $remind_at 提醒时间
 * @property User $user
 */
class GoodsRemind extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_remind}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'user_id', 'created_at', 'updated_at', 'deleted_at', 'remind_at'], 'required'],
            [['mall_id', 'goods_id', 'user_id', 'is_remind', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at', 'remind_at'], 'safe'],
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
            'goods_id' => 'Goods ID',
            'user_id' => 'User ID',
            'is_remind' => '是否提醒',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'remind_at' => '提醒时间',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
