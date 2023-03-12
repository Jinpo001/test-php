<?php

namespace app\plugins\teller\models;

use Yii;
use app\models\Order;
use app\models\RechargeOrders;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerSales;

/**
 * This is the model class for table "{{%teller_push_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $user_type 用户类型
 * @property string $order_type 订单类型
 * @property int $order_id 订单ID
 * @property int $teller_order_id 收银台订单ID
 * @property int $re_order_id 充值订单ID
 * @property string $push_type 提成类型
 * @property string $push_order_money 按订单提成金额 
 * @property string $push_percent 按百分比提成
 * @property int $sales_id 导购员ID
 * @property int $cashier_id 收银员ID
 * @property string $push_money 订单过售后最终提成金额
 * @property string $status 订单状态
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class TellerPushOrder extends \app\models\ModelActiveRecord
{
    const ORDER_STATUS_FINISH = 'finish'; // 已结算
    const ORDER_STATUS_PENDING = 'pending'; // 待结算

    const ORDER_TYPE_ORDER = 'order'; // 买单
    const ORDER_TYPE_RECHARGE = 'recharge'; // 会员充值

    const PUSH_TYPE_ORDER = 'order';
    const PUSH_TYPE_PERCENT = 'percent';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teller_push_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'order_id', 're_order_id', 'sales_id', 'cashier_id', 'is_delete', 'teller_order_id'], 'integer'],
            [['push_order_money', 'push_percent', 'push_money'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['user_type', 'order_type', 'push_type'], 'string', 'max' => 65],
            [['status'], 'string', 'max' => 255],
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
            'user_type' => '用户类型',
            'order_type' => '订单类型',
            'order_id' => '订单ID',
            're_order_id' => '充值订单ID',
            'teller_order_id' => '收银台订单ID',
            'push_type' => '提成类型',
            'push_order_money' => '按订单提成金额 ',
            'push_percent' => '按百分比提成',
            'sales_id' => '导购员ID',
            'cashier_id' => '收银员ID',
            'push_money' => '订单过售后最终提成金额',
            'status' => '订单状态',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCashier()
    {
        return $this->hasOne(TellerCashier::className(), ['id' => 'cashier_id']);
    }

    public function getSales()
    {
        return $this->hasOne(TellerSales::className(), ['id' => 'sales_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getReOrder()
    {
        return $this->hasOne(RechargeOrders::className(), ['id' => 're_order_id']);
    }

    public function getTellerOrder()
    {
        return $this->hasOne(TellerOrders::className(), ['id' => 'teller_order_id']);
    }
}
