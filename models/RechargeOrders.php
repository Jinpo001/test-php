<?php

namespace app\models;

use app\models\PaymentOrder;

/**
 * This is the model class for table "{{%recharge_orders}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $order_no
 * @property int $user_id
 * @property string $pay_price
 * @property string $send_price
 * @property int $pay_type 支付方式 1.线上支付 | 2.pos机 | 3.现金
 * @property int $is_pay
 * @property string $pay_time
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $send_integral 赠送的积分
 * @property int $send_member_id 赠送的积分
 * @property string $send_coupon 赠送的优惠券
 * @property string $send_card 赠送的卡券
 * @property int $send_type 赠送的卡券
 * @property int $send_lottery
 * @property int $lottery_limit
 */
class RechargeOrders extends ModelActiveRecord
{
    /**
     * 支付方式: 线上支付
     */
    const PAY_TYPE_ON_LINE = 1;
    const PAY_TYPE_POS = 2; // pos机
    const PAY_TYPE_CASH = 3; // 现金

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%recharge_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'pay_price', 'send_price', 'pay_type', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'pay_type', 'is_pay', 'is_delete', 'send_integral', 'send_member_id', 'send_type', 'lottery_limit'], 'integer'],
            [['pay_price', 'send_price'], 'number'],
            [['pay_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['order_no'], 'string', 'max' => 32],
            [['send_coupon','send_card'], 'string'],
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
            'order_no' => 'Order No',
            'user_id' => 'User ID',
            'pay_price' => 'Pay Price',
            'send_price' => 'Send Price',
            'pay_type' => 'Pay Type', // 支付方式 1.线上支付 | 2.pos机 | 3.现金
            'is_pay' => 'Is Pay',
            'pay_time' => 'Pay Time',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'send_integral' => '赠送的积分',
            'send_member_id' => '赠送的会员',
            'send_coupon' =>  '赠送的优惠券',
            'send_card' => '赠送的卡券',
            'send_type' => '赠送类型',
            'lottery_limit' => '赠送抽奖次数',
        ];
    }

    public function getPaymentOrder()
    {
        return $this->hasOne(PaymentOrder::className(), ['order_no' => 'order_no'])->andWhere(['is_pay' => 1]);
    }
}
