<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/14
 * Time: 15:49
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;


use app\events\OrderEvent;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\forms\common\template\order_pay_template\OrderSendInfo;
use app\jobs\OrderConfirmJob;
use app\models\Order;
use app\models\OrderDetailExpress;

class OrderSentHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_SENT, function ($event) {
            /** @var OrderEvent $event */
            //社区团购不执行该事件
            if ($event->order->sign == 'community') {
                return;
            }
            \Yii::$app->setMchId($event->order->mch_id);
            $orderAutoConfirmTime = \Yii::$app->mall->getMallSettingOne('delivery_time');

            // 发送模板消息
            $this->sendTemplate($event->order);
            $this->sendSmsToUser($event->order);

            if (is_numeric($orderAutoConfirmTime) && $orderAutoConfirmTime >= 0) {
                // 订单自动收货任务
                \Yii::$app->queue->delay($orderAutoConfirmTime * 86400)->push(new OrderConfirmJob([
                    'orderId' => $event->order->id
                ]));
                $autoConfirmTime = strtotime($event->order->send_time) + $orderAutoConfirmTime * 86400;
                $event->order->auto_confirm_time = mysql_timestamp($autoConfirmTime);
                $event->order->save();
            }
        });
    }

    public function sendTemplate($order)
    {
        try {
            $goodsName = '';
            foreach ($order->detail as $item) {
                $goodsName .= $item['goods']['name'];
            }

            $express = '';
            $expressNo = '';
            $merchantRemark = '';
            /** @var OrderDetailExpress $orderDetailExpress */
            $orderDetailExpress = OrderDetailExpress::find()->where(['order_id' => $order->id])
                ->orderBy(['created_at' => SORT_DESC])->one();
            if ($orderDetailExpress) {
                if ($orderDetailExpress->send_type == 1) {
                    $express = $orderDetailExpress->express;
                    $expressNo = $orderDetailExpress->express_no;
                    $merchantRemark = $orderDetailExpress->merchant_remark;
                } else {
                    $merchantRemark = $orderDetailExpress->express_content;
                }
            }

            TemplateList::getInstance()->getTemplateClass(OrderSendInfo::TPL_NAME)->send([
                'name' => $goodsName,
                'express' => $express ? $express : '商家自己发货',
                'express_no' => $expressNo ? $expressNo : '123456',
                'remark' => $merchantRemark ? $merchantRemark : '商品已发货，注意查收！',
                'user' => $order->user,
                'page' => 'pages/order/index/index?status=3'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param Order $order
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($order)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$order->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $order->user;
            $messageService->content = [
                'mch_id' => $order->mch_id,
                'args' => [substr($order->order_no, -6)]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($order->user);
            $messageService->tplKey = OrderSendInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
