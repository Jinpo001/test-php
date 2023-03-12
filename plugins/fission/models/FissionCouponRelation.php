<?php

namespace app\plugins\fission\models;

use Yii;

/**
 * This is the model class for table "{{%fission_coupon_relation}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $activity_log_id
 * @property int $reward_log_id
 * @property int $user_coupon_id
 * @property string $created_at
 */
class FissionCouponRelation extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fission_coupon_relation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'activity_log_id', 'reward_log_id', 'user_coupon_id'], 'required'],
            [['mall_id', 'activity_log_id', 'reward_log_id', 'user_coupon_id'], 'integer'],
            [['created_at'], 'safe'],
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
            'activity_log_id' => 'Activity ID',
            'reward_log_id' => 'Reward ID',
            'user_coupon_id' => 'User Coupon ID',
            'created_at' => 'Created At',
        ];
    }
}
