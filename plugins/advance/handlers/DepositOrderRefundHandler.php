<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/29
 * Time: 11:02
 */

namespace app\plugins\advance\handlers;

use app\forms\common\template\TemplateList;
use app\forms\common\template\order_pay_template\OrderCancelInfo;
use app\handlers\HandlerBase;
use app\models\GoodsAttr;
use app\plugins\advance\events\DepositEvent;
use app\plugins\advance\models\AdvanceOrder;

class DepositOrderRefundHandler extends HandlerBase
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(AdvanceOrder::EVENT_REFUND, function ($event) {
            /** @var DepositEvent $event */
            $t = \Yii::$app->db->beginTransaction();
            try {
                //已支付的退定金
                if ($event->advanceOrder->is_pay == 1 && $event->advanceOrder->is_cancel == 1) {
                    \Yii::$app->payment->refund($event->advanceOrder->advance_no, $event->advanceOrder->deposit*$event->advanceOrder->goods_num);
                    $event->advanceOrder->is_refund = 1;
                    $event->advanceOrder->save();
                    (new GoodsAttr())->updateStock(
                        $event->advanceOrder->goods_num,
                        'add',
                        $event->advanceOrder->goods_id,
                        $event->advanceOrder->goods_attr_id
                    );//返回库存
                    $t->commit();
                    // 退款成功发送模版消息
                    $this->sendTemplate($event->advanceOrder);
                } else {
                    return ;
                }
            } catch (\Exception $exception) {
                $t->rollBack();
                \Yii::error('订单售后退定金事件：');
                \Yii::error($exception);
                throw $exception;
            }

        });
    }

    private function sendTemplate($order, $price = null)
    {
        try {
            $goodsName = '预售退款';
            $remark = '商家取消订单，定金退回';
            $money = $price ?? $order->deposit * $order->goods_num;

            TemplateList::getInstance()->getTemplateClass(OrderCancelInfo::TPL_NAME)->send([
                'goodsName' => $goodsName,
                'order_no' => $order->advance_no,
                'price' => price_format($money),
                'remark' => $remark,
                'user' => $order->user,
                'page' => 'pages/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }
}
