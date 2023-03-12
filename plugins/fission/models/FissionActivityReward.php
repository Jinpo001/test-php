<?php

namespace app\plugins\fission\models;

use Yii;

/**
 * This is the model class for table "{{%fission_activity_reward}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $activity_id
 * @property int $type 类型0--红包奖励 1--关卡一奖励 2--关卡二奖励 3--关卡三奖励 4关卡四奖励 5关卡五奖励
 * @property string $status 奖励种类  cash--现金  balance--余额 coupon--优惠券 integer—积分 goods—赠品 card—卡券
 * @property int $people_number 邀请人数
 * @property int $model_id 赠品、卡券、优惠券时为奖励的id
 * @property string $exchange_type 兑奖方式 online--线上 offline--线下
 * @property string $min_number 现金、余额、积分奖励时，最小值
 * @property string $max_number 现金、余额、积分奖励时最大值
 * @property string $send_type 数量发放方式random--随机金额。average--平均
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $level 奖励的等级 main--主要奖励 secondary--次要奖励
 * @property int $attr_id 奖励为赠品时，规格id
 */
class FissionActivityReward extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fission_activity_reward}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'activity_id', 'type', 'people_number', 'model_id', 'is_delete', 'attr_id'], 'integer'],
            [['min_number', 'max_number'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['status', 'exchange_type', 'send_type', 'level'], 'string', 'max' => 255],
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
            'activity_id' => 'Activity ID',
            'type' => '类型0--红包奖励 1--关卡一奖励 2--关卡二奖励 3--关卡三奖励 4关卡四奖励 5关卡五奖励',
            'status' => '奖励种类 
cash--现金 
balance--余额
coupon--优惠券
integer—积分
goods—赠品
card—卡券',
            'people_number' => '邀请人数',
            'model_id' => '赠品、卡券、优惠券时为奖励的id',
            'exchange_type' => '兑奖方式 online--线上 offline--线下',
            'min_number' => '现金、余额、积分奖励时，最小值',
            'max_number' => '现金、余额、积分奖励时最大值',
            'send_type' => '数量发放方式random--随机金额。average--平均',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'level' => '奖励的等级 main--主要奖励 secondary--次要奖励',
            'attr_id' => '奖励为赠品时，规格id',
        ];
    }
}
