<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 10:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;

use app\models\Order;

class OrderPayedHandlerClass extends BaseOrderPayedHandler
{
    public function handle()
    {
        \Yii::error('mall order payed');
        self::execute();
    }

    protected function execute()
    {
        $this->user = $this->event->order->user;
        /**
         * 订单是否取消状态（针对调起支付后，自动取消的订单）
         * 退款且不执行后续操作
         * $order 重新查一次最新的订单数据
         */
        $order = Order::findOne($this->event->order->id);
        if ($order->cancel_status == 1 && ($order->pay_type == 1 || $order->pay_type == 3)) {
            try {
                $res = \Yii::$app->payment->refund($order->order_no, $order->total_pay_price);
                if ($res) {
                    $order->seller_remark = '订单已过可支付时间，订单取消并且已退款';
                } else {
                    $order->seller_remark = '订单已过可支付时间，未退款';
                }
            } catch (\Exception $exception) {
                $order->seller_remark = '订单已过可支付时间，未退款';
            }
            $order->cancel_time = mysql_timestamp();
            $order->save();
            return $this;
        } else {
            if ($this->event->order->pay_type == 2) {
                if ($this->event->order->is_pay == 0) {
                    // 支付方式：货到付款未支付时，只触发部分通知类
                    static::notice();
                } else {
                    // 支付方式：货到付款，订单支付时，触发剩余部分
                    static::pay();
                }
            } else {
                static::notice();
                static::pay();
            }
        }
        return $this;
    }

    protected function notice()
    {
        \Yii::error('--mall notice--');
        $this->sendSms()->sendMail()->receiptPrint('pay')
            ->sendTemplate()->sendMpTemplate()->sendTemplateMsgToMch()->sendBuyPrompt()->setGoods()->sendSmsToUser()->addShareOrder();
        return $this;
    }

    protected function pay()
    {
        \Yii::error('--mall pay--');
        // 首次付款绑定下级--生成分销订单--下单用户成为分销商--设置卡密数据
        $this->saveResult()->becomeJuniorByFirstPay()->addShareOrder()->becomeShare()->setTypeData();
        return $this;
    }
}
