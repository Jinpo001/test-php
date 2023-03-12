<?php

namespace app\plugins\teller\models;

use Yii;
use app\models\Order;
use app\models\RechargeOrders;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerSales;
use app\plugins\teller\models\TellerWorkLog;

/**
 * This is the model class for table "{{%teller_orders}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $order_id 订单ID
 * @property int $re_order_id 充值订单ID
 * @property int $cashier_id 收银员ID
 * @property int $sales_id 导购员ID
 * @property int $order_query 付款码订单查询次数
 * @property string $order_type 订单类型
 * @property string $add_money 订单加价
 * @property string $change_price 订单改价金额
 * @property string $change_price_type 订单改价类型
 * @property string $created_at
 * @property string $updated_at
 * @property int $is_pay 是否支付
 * @property int $pay_type 支付类型
 * @property int $is_refund 是否退款
 * @property string $refund_money 退款总金额
 * @property int $work_log_id 交班记录ID
 * @property int $is_statistics 是否统计
 */
class TellerOrders extends \app\models\ModelActiveRecord
{
    const ORDER_TYPE_ORDER = 'order'; // 买单
    const ORDER_TYPE_RECHARGE = 'recharge'; // 会员充值
    const ORDER_TYPE_REFUND = 'refund';

    const ORDER_TYPE_LIST = [
        self::ORDER_TYPE_ORDER => '买单',
        self::ORDER_TYPE_RECHARGE => '会员充值',
        self::ORDER_TYPE_REFUND => '退款',
    ];

    const CHANGE_PRICE_TYPE_ADD = 'add'; // 加价
    const CHANGE_PRICE_TYPE_SUBTRACT = 'subtract'; // 减价

    const CHANGE_PRICE_TYPE_LIST = [
        self::CHANGE_PRICE_TYPE_ADD => '加价',
        self::CHANGE_PRICE_TYPE_SUBTRACT => '减价',
    ];
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teller_orders}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'order_id', 're_order_id', 'cashier_id', 'sales_id', 'order_query', 'is_pay', 'pay_type', 'is_refund', 'work_log_id', 'is_statistics'], 'integer'],
            [['order_type', 'change_price_type'], 'string'],
            [['add_money', 'change_price', 'refund_money'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
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
            'order_id' => '订单ID',
            're_order_id' => '充值订单ID',
            'cashier_id' => '收银员ID',
            'sales_id' => '导购员ID',
            'order_type' => '订单类型',
            'add_money' => '订单加价',
            'order_query' => '付款码订单查询次数',
            'change_price' => '订单改价金额',
            'change_price_type' => '订单改价类型',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_pay' => '是否支付',
            'pay_type' => '支付类型',
            'is_refund' => '是否退款0.否|1.是',
            'refund_money' => '退款总金额',
            'work_log_id' => '交班记录ID',
            'is_statistics' => '是否统计',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getReOrder()
    {
        return $this->hasOne(RechargeOrders::className(), ['id' => 're_order_id']);
    }

    public function getCashier()
    {
        return $this->hasOne(TellerCashier::className(), ['id' => 'cashier_id']);
    }

    public function getSales()
    {
        return $this->hasOne(TellerSales::className(), ['id' => 'sales_id']);
    }

    public function getWorkLog()
    {
        return $this->hasOne(TellerWorkLog::className(), ['id' => 'work_log_id']);
    }


    public function getPayWay($payType)
    {
        switch ($payType) {
            case 3:
                return '余额支付';
                break;
            case 9:
                return '现金支付';
                break;
            case 10:
                return 'pos机支付';
                break;
            case 11:
                return '微信支付';
                break;
            case 12:
                return '支付宝支付';
                break;
            default:
                return '未知';
                break;
        }
    }
}
