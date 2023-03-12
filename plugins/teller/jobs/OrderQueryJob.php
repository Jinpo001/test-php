<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\jobs;

use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\jobs\BaseJob;
use app\models\PaymentOrderUnion;
use app\plugins\teller\forms\OrderQueryForm;
use app\plugins\teller\forms\web\order\TellerRechargePayNotify;
use app\plugins\teller\models\TellerOrders;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderQueryJob extends BaseJob implements JobInterface
{
    public $id;
    public $pay_type;

    public function execute($queue)
    {
        \Yii::warning('付款码支付查询开始');
        $this->setRequest();
        try {
            if (!in_array($this->pay_type, [Payment::PAY_TYPE_WECHAT_SCAN, Payment::PAY_TYPE_ALIPAY_SCAN])) {
                throw new \Exception('付款码支付类型未知' . $this->pay_type);
            }
            $orderUnion = PaymentOrderUnion::find()->andWhere(['id' => $this->id])->with(['paymentOrder.order', 'paymentOrder.reOrder'])->one();

            if (!$orderUnion) {
                throw new \Exception('付款码支付订单不存在');
            }

            if ($orderUnion->is_pay == 1) {
                throw new \Exception('付款码支付订单已处理，无需重复');
            }

            // TODO 订单已取消 又支付的情况 需要退款

            $paymentOrder = $orderUnion->paymentOrder[0];
            if ($paymentOrder->order) {
                $tellerOrder = TellerOrders::find()->andWhere([
                    'order_id' => $paymentOrder->order->id
                ])->one();
            } elseif ($paymentOrder->reOrder) {
                $tellerOrder = TellerOrders::find()->andWhere([
                    're_order_id' => $paymentOrder->reOrder->id
                ])->one();
            } else {
                throw new \Exception('订单类型异常');
            }

            if (!$tellerOrder) {
                throw new \Exception('订单不存在');
            }

            $orderQueryForm = new OrderQueryForm();
            $orderQueryForm->id= $orderUnion->id;
            $orderQueryForm->pay_type = $this->pay_type;
            $res = $orderQueryForm->getQueryOrder();

            // 订单未支付时定时查询
            if ($res['pay_status'] == 0) {
                // 最多查询6次
                if ($tellerOrder->order_query >= 6) {
                    // TODO 需要撤销订单
                    return false;
                }
                // 队列主动查询
                $second = $tellerOrder->order_query == 0 ? 5 : $tellerOrder->order_query * 10;
                \Yii::$app->queue->delay($second)->push(new OrderQueryJob([
                    'id' => $this->id,
                    'pay_type' => $this->pay_type
                ]));
                
                $tellerOrder->order_query = $tellerOrder->order_query + 1;
                $tellerOrder->save();
                \Yii::warning('付款码支付查询进行中');
            } elseif ($res['pay_status'] == 1) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $payType = $this->pay_type == Payment::PAY_TYPE_WECHAT_SCAN ? 11 : 12;
                    $orderUnion->is_pay = 1;
                    $orderUnion->pay_type = $payType;
                    $orderUnion->save();

                    $paymentOrder->is_pay = 1;
                    $paymentOrder->pay_type = $payType;
                    $paymentOrder->save();

                    $transaction->commit();
                } catch(\Exception $exception) {
                    $transaction->rollBack();
                    throw $exception;
                }

                $NotifyClass = $paymentOrder->notify_class;
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => $this->pay_type,
                ]);
                
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $exception) {
                    \Yii::error($exception->getMessage());
                }
            } elseif ($res['pay_status' == 2]) {
                \Yii::warning('用户取消支付');
            } else {
                \Yii::warning('付款状态未知');
                \Yii::warning($res);
            }
            \Yii::warning('付款码支付查询结束');
        } catch (\Exception $e) {
            \Yii::warning('付款码支付查询异常');
            \Yii::warning($e);
            $this->checkOrder();
            
        }
    }

    private function checkOrder()
    {
        $orderUnion = PaymentOrderUnion::find()->andWhere(['id' => $this->id])->with(['paymentOrder.order', 'paymentOrder.reOrder'])->one();

        if (!$orderUnion) {
            return false;
        }
        $endTime = strtotime($orderUnion->created_at) + 600;
        if ($orderUnion->is_pay != 1 && $endTime > time()) {
            \Yii::$app->queue->delay(10)->push(new OrderQueryJob([
                'id' => $this->id,
                'pay_type' => $this->pay_type,
            ])); 
        } 
    }
}