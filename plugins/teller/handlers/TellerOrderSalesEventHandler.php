<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\handlers;

use app\handlers\orderHandler\BaseOrderSalesHandler;
use app\models\Model;
use app\models\OrderRefund;
use app\plugins\teller\Plugin;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerPushOrder;
use app\plugins\teller\models\TellerSales;

class TellerOrderSalesEventHandler extends BaseOrderSalesHandler
{
    protected function action()
    {
        if ($this->order->sign == (new Plugin())->getName()) {
            \Yii::warning('收银台售后事件开始');
            $this->setPushOrder();
            parent::action();
        }
    }

    public function setPushOrder()
    {
        \Yii::warning('收银台提成结算');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $order = $this->event->order;

            $pushOrders = TellerPushOrder::find()->andWhere([
                'order_id' => $order->id,
                'order_type' => TellerPushOrder::ORDER_TYPE_ORDER,
                'mall_id' => $order->mall_id,
                'is_delete' => 0
            ])->with('order', 'tellerOrder')->all();

            foreach ($pushOrders as $pushOrder) {
                $pushOrder->status = TellerPushOrder::ORDER_STATUS_FINISH;
                switch ($pushOrder->push_type) {
                    // 按订单
                    case TellerPushOrder::PUSH_TYPE_ORDER:
                        // 有部分退款的情况 按退款比例给提成
                        if ($pushOrder->tellerOrder->refund_money) {
                            $surplusPrice = price_format($pushOrder->order->total_pay_price - $pushOrder->tellerOrder->refund_money);
                            $percent = price_format($surplusPrice / $pushOrder->order->total_pay_price);

                            $pushOrder->push_money = price_format($pushOrder->push_order_money * $percent);
                        } else {
                            $pushOrder->push_money = $pushOrder->push_order_money;
                        }
                        break;
                    // 按订单金额百分比
                    case TellerPushOrder::PUSH_TYPE_PERCENT:
                        $totalPayPrice = $pushOrder->order->total_pay_price - $pushOrder->tellerOrder->refund_money;
                        $totalPayPrice = $totalPayPrice ?: 0;
                        $pushMoney = $totalPayPrice * ($pushOrder->push_percent / 100);
                        $pushOrder->push_money = price_format($pushMoney);
                        break;
                    default:
                        \Yii::error(sprintf('提成订单%s未知提成类型%s', $pushOrder->id, $pushOrder->push_type));
                        break;
                }
                $pushOrder->save();

                if ($pushOrder->user_type == TellerCashier::USER_TYPE) {
                    $cashier = TellerCashier::findOne($pushOrder->cashier_id);

                    if (!$cashier) {
                        throw new \Exception(sprintf('收银员不存在%s', $pushOrder->cashier_id));
                    }

                    $cashier->push_money = $cashier->push_money + $pushOrder->push_money;
                    $cashier->sale_money = $cashier->sale_money + ($pushOrder->order->total_pay_price - $pushOrder->tellerOrder->refund_money);
                    $res = $cashier->save();

                    if (!$res) {
                        throw new \Exception((new Model())->getErrorMsg($cashier));
                    }

                } else {
                    $sales = TellerSales::findOne($pushOrder->sales_id);

                    if (!$sales) {
                        throw new \Exception(sprintf('导购员不存在%s', $pushOrder->sales_id));
                    }

                    $sales->push_money = $sales->push_money + $pushOrder->push_money;
                    $sales->sale_money = $sales->sale_money + ($pushOrder->order->total_pay_price - $pushOrder->tellerOrder->refund_money);
                    $res = $sales->save();

                    if (!$res) {
                        throw new \Exception((new Model())->getErrorMsg($sales));
                    }
                }
            }

            $transaction->commit();
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::warning(sprintf('收银台提成结算出错,订单ID%s', $this->event->order->id));
            \Yii::warning($exception);
        }
    }
}
