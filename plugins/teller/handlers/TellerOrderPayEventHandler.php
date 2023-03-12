<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\handlers;


use app\events\OrderEvent;
use app\forms\api\order\OrderException;
use app\handlers\orderHandler\OrderPayedHandlerClass;
use app\models\Model;
use app\models\Order;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\forms\common\PushOrder;
use app\plugins\teller\jobs\TellerPrintJob;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerPushOrder;
use app\plugins\teller\models\TellerSales;
use app\plugins\teller\models\TellerWorkLog;

class TellerOrderPayEventHandler extends OrderPayedHandlerClass
{
    public function handle()
    {
        self::execute();
    }

    protected function execute()
    {
        $this->user = $this->event->order->user;
        self::notice();
        self::pay();
    }

    protected function notice()
    {
       \Yii::error('--mall notice--');
        $this->sendSms()->sendMail()->receiptPrint('pay')->sendBuyPrompt()->setGoods()->sendSmsToUser()->addShareOrder();
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--teller pay--');
        $this->pushOrder($this->event->order);
        $this->updateOrderStatus();
        $this->setWorkLog($this->event->order);
        return parent::pay();
    }

    // 统计订单
    private function setWorkLog($order)
    {
        \Yii::warning('收银台订单统计开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tellerOrder = TellerOrders::find()->andWhere([
                'order_id' => $order->id
            ])->with(['sales', 'cashier', 'order', 'workLog'])->one();

            if (!$tellerOrder) {
                throw new \Exception('收银台订单不存在');
            }

            $tellerOrder->is_statistics = 1;
            $res = $tellerOrder->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($tellerOrder));
            }

            $workLog = $tellerOrder->workLog;
            if (!$workLog) {
                throw new \Exception('无交班记录');
            }

            $extra = json_decode($workLog->extra_attributes, true);
            $payPrice = $order->total_pay_price;
            $extra['proceeds']['total_proceeds'] = price_format($extra['proceeds']['total_proceeds'] + $payPrice);
            $extra['proceeds']['total_order'] += 1;
            switch ($order->paymentOrder->pay_type) {
                case 3:
                    $extra['proceeds']['balance_proceeds'] = price_format($extra['proceeds']['balance_proceeds'] + $payPrice);
                    break;
                case 9:
                    $extra['proceeds']['cash_proceeds'] = price_format($extra['proceeds']['cash_proceeds'] + $payPrice);
                    break;
                case 10:
                    $extra['proceeds']['pos_proceeds'] = price_format($extra['proceeds']['pos_proceeds'] + $payPrice);
                    break;
                case 11:
                    $extra['proceeds']['wechat_proceeds'] = price_format($extra['proceeds']['wechat_proceeds'] + $payPrice);
                    break;
                case 12:
                    $extra['proceeds']['alipay_proceeds'] = price_format($extra['proceeds']['alipay_proceeds'] + $payPrice);
                    break;
                default:
                    throw new \Exception('统计未知支付类型' . $order->paymentOrder->pay_type);
                    break;
            }
            $workLog->extra_attributes = json_encode($extra);
            $res = $workLog->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($workLog));
            }

            $transaction->commit();
            \Yii::warning('收银台订单统计结束');
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('收银台订单统计异常');
            \Yii::error($exception);
        }
    }

    public function updateOrderStatus()
    {
        \Yii::warning('收银台订单状态更新开始');
        $order = $this->event->order;
        $order->is_send = 1;
        $order->send_time = mysql_timestamp();

        $order->is_confirm = 1;
        $order->confirm_time = mysql_timestamp();

        $order->status = 1;
        $order->is_delete = 0;
        $res = $order->save();
        if (!$res) {
            \Yii::error('收银台下单状态更新失败' . $this->getErrorMsg($order));
        }


        \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent(['order' => $order]));
    }

    // 生成提成订单
    private function pushOrder($order)
    {
        \Yii::warning('收银台提成订单生成开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tellerOrder = TellerOrders::find()->andWhere([
                'order_id' => $order->id
            ])->with(['sales', 'cashier', 'order'])->one();

            $tellerOrder->is_pay = 1;
            $tellerOrder->pay_type = $order->paymentOrder->pay_type;
            $tellerOrder->save();

            $common = new CommonTellerSetting();
            $common->mall_id = $tellerOrder->mall_id;
            $setting = $common->search();

            // 收银员提成
            if ($setting['is_cashier_push'] && $tellerOrder->cashier) {
                $pushOrder = new TellerPushOrder();
                $pushOrder->mall_id = $tellerOrder->mall_id;
                $pushOrder->mch_id = $tellerOrder->mch_id;
                $pushOrder->user_type = TellerCashier::USER_TYPE;
                $pushOrder->order_type = TellerPushOrder::ORDER_TYPE_ORDER;
                $pushOrder->order_id = $tellerOrder->order_id;
                $pushOrder->push_type = $setting['cashier_push_type'] == 1 ? TellerPushOrder::PUSH_TYPE_ORDER : TellerPushOrder::PUSH_TYPE_PERCENT;
                $pushOrder->push_order_money = $setting['cashier_push'];
                $pushOrder->push_percent = $setting['cashier_push_percent'];
                $pushOrder->cashier_id = $tellerOrder->cashier->id;
                $pushOrder->status = TellerPushOrder::ORDER_STATUS_PENDING;
                $pushOrder->teller_order_id = $tellerOrder->id;
                $res = $pushOrder->save();
                
                if (!$res) {
                    throw new OrderException((new Model())->getErrorMsg($pushOrder));
                }
            }

            if ($setting['is_sales_push'] && $tellerOrder->sales) {
                $pushOrder = new TellerPushOrder();
                $pushOrder->mall_id = $tellerOrder->mall_id;
                $pushOrder->mch_id = $tellerOrder->mch_id;
                $pushOrder->user_type = TellerSales::USER_TYPE;
                $pushOrder->order_type = TellerPushOrder::ORDER_TYPE_ORDER;
                $pushOrder->order_id = $tellerOrder->order_id;
                $pushOrder->push_type = $setting['sales_push_type'] == 1 ? TellerPushOrder::PUSH_TYPE_ORDER : TellerPushOrder::PUSH_TYPE_PERCENT;
                $pushOrder->push_order_money = $setting['sales_push'];
                $pushOrder->push_percent = $setting['sales_push_percent'];
                $pushOrder->sales_id = $tellerOrder->sales->id;
                $pushOrder->status = TellerPushOrder::ORDER_STATUS_PENDING;
                $pushOrder->teller_order_id = $tellerOrder->id;
                $res = $pushOrder->save();

                if (!$res) {
                    throw new OrderException((new Model())->getErrorMsg($pushOrder));
                }
            }
            $transaction->commit();
            \Yii::warning('收银台提成订单生成结束');
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('收银台提成订单生成异常');
            \Yii::error($exception);
        }
    }

    /**
     * @param string $orderType order|pay|confirm 打印方式
     * @return $this
     * 小票打印
     */
    protected function receiptPrint($orderType)
    {
        try {
            $job = new TellerPrintJob();
            $job->mall = \Yii::$app->mall;
            $job->order = $this->event->order;
            $job->orderType = $orderType;
            \Yii::$app->queue->delay(0)->push($job);
        } catch (\Exception $exception) {
            \Yii::error('小票打印机打印:' . $exception->getMessage());
        }
        return $this;
    }
}
