<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web\order;


use app\core\payment\PaymentOrder;
use app\core\response\ApiCode;
use app\models\Model;
use app\models\Order;
use app\models\Recharge;
use app\models\RechargeOrders;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\forms\web\order\TellerRechargePayNotify;
use app\plugins\teller\jobs\RechargeOrderQueryJob;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerSales;
use app\plugins\teller\models\TellerWorkLog;
use yii\db\Exception;

class TellerRechargeOrderForm extends Model
{
    public $pay_price;
    public $id;
    public $pay_type;
    public $sales_id;
    public $user_id;

    public function rules()
    {
        return [
            [['pay_type', 'user_id'], 'required'],
            [['pay_type', 'pay_price'], 'string'],
            [['id', 'sales_id'], 'integer'],
        ];
    }

    public function balanceRecharge()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {            
            $reOrder = new RechargeOrders();
            $reOrder->mall_id = \Yii::$app->mall->id;
            $reOrder->order_no = Order::getOrderNo('RE');
            $reOrder->user_id = $this->user_id;
            if ($this->id) {
                $recharge = Recharge::findOne([
                    'id' => $this->id,
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0
                ]);
                if (!$recharge) {
                    throw new Exception('充值方案异常');
                }
                $reOrder->send_type = $recharge->send_type;;
                $reOrder->send_coupon = $recharge->send_coupon;
                $reOrder->send_card = $recharge->send_card;
                $reOrder->lottery_limit = $recharge->lottery_limit;
                $reOrder->pay_price = $recharge->pay_price;
                $reOrder->send_price = $recharge->send_price;
                $reOrder->send_integral = $recharge->send_integral;
                $reOrder->send_member_id = $recharge->send_member_id;
            } else {
                if (!$this->pay_price) {
                    throw new \Exception('请传入充值金额');
                }

                $reOrder->send_type = 0;
                $reOrder->lottery_limit = 0;
                $reOrder->send_coupon = '';
                $reOrder->send_card = '';
                $reOrder->pay_price = $this->pay_price;
                $reOrder->send_price = 0;
                $reOrder->send_integral = 0;
                $reOrder->send_member_id = 0;
            }

            $wechatScan = \app\core\payment\Payment::PAY_TYPE_WECHAT_SCAN;
            $alipayScan = \app\core\payment\Payment::PAY_TYPE_ALIPAY_SCAN;
            $pos = \app\core\payment\Payment::PAY_TYPE_POS;
            $cash = \app\core\payment\Payment::PAY_TYPE_CASH;

            $supportPayTypes = [
                $wechatScan,
                $alipayScan,
                $pos,
                $cash
            ];

            $setting = (new CommonTellerSetting())->search();

            if (!$setting['is_member_topup']) {
                throw new \Exception('未开启会员充值');
            }

            $supportPayTypes = array_intersect($supportPayTypes, $setting['payment_type']);

            if (!in_array($this->pay_type, $supportPayTypes)) {
                throw new \Exception('支付方式不支持');
            }

            switch ($this->pay_type) {
                case $wechatScan:
                    $orderPayType = RechargeOrders::PAY_TYPE_ON_LINE;
                    break;
                case $alipayScan:
                    $orderPayType = RechargeOrders::PAY_TYPE_ON_LINE;
                    break;
                case $pos:
                    $orderPayType = RechargeOrders::PAY_TYPE_POS;
                    break;
                case $cash:
                    $orderPayType = RechargeOrders::PAY_TYPE_CASH;
                    break;
                default:
                    throw new \Exception('支付方式不支持');
                    break;
            }

            $reOrder->pay_type = $orderPayType;
            $res = $reOrder->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($reOrder));
            }

            $tellerOrder = new TellerOrders();
            $tellerOrder->mall_id = $reOrder->mall_id;
            $tellerOrder->mch_id = \Yii::$app->user->identity->mch_id;
            $tellerOrder->re_order_id = $reOrder->id;
            $tellerOrder->order_type = TellerOrders::ORDER_TYPE_RECHARGE;

            $cashier = TellerCashier::findOne(['user_id' => \Yii::$app->user->id]);
            if ($cashier) {
                $tellerOrder->cashier_id = $cashier->id;
            }

            $sales = TellerSales::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'id' => $this->sales_id,
                'is_delete' => 0,
            ])->one();
            if ($sales) {
                $tellerOrder->sales_id = $sales->id;
            }

            // 关联交班记录
            $workLog = TellerWorkLog::find()->andWhere([
                'mall_id' => $tellerOrder->mall_id,
                'mch_id' => $tellerOrder->mch_id,
                'cashier_id' => $cashier->id,
                'is_delete' => 0,
                'status' => TellerWorkLog::PENDING
            ])->one();

            if ($workLog) {
                $tellerOrder->work_log_id = $workLog->id;
            }

            $tellerOrder->save();

            $payOrder = new PaymentOrder([
                'title' => '余额充值',
                'amount' => floatval($reOrder->pay_price),
                'orderNo' => $reOrder->order_no,
                'notifyClass' => TellerRechargePayNotify::class,
                'supportPayTypes' => $supportPayTypes,
            ]);
            $id = \Yii::$app->payment->createOrder($payOrder);

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '订单创建成功',
                'data' => [
                    'pay_id' => $id
                ]
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }
}
