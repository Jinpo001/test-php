<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\handlers;

use app\events\OrderEvent;
use app\events\OrderRefundEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\order\CommonOrder;
use app\forms\common\share\AddShareOrder;
use app\jobs\ChangeShareOrderJob;
use app\jobs\OrderSalesJob;
use app\models\CoreQueueData;
use app\models\GoodsAttr;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\ShareOrder;
use app\models\UserCard;
use yii\db\Exception;

class OrderRefundConfirmedHandler extends HandlerBase
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_REFUND, function ($event) {
            $this->confirmOrder($event->order_refund->id);
            $this->finishOrder($event->order_refund->id);
            /** @var OrderRefundEvent $event */
            \Yii::$app->setMchId($event->order_refund->mch_id);
            $orderDetail = $event->order_refund->detail;
            $orderDetail->refund_status = 2;
            // 商家同意退款 销毁订单商品赠送的卡券
            if (in_array($event->order_refund->type, [1,3]) && $event->order_refund->status == 2) {
                $orderDetail->is_refund = 1;
                /* @var UserCard[] $userCards */
                $userCards = UserCard::find()->where([
                    'order_id' => $event->order_refund->order_id,
                    'order_detail_id' => $event->order_refund->order_detail_id
                ])->all();

                foreach ($userCards as $userCard) {
                    $userCard->is_delete = 1;
                    $userCard->card->updateCount('add', 1);
                    $res = $userCard->save();
                    if (!$res) {
                        \Yii::error('卡券销毁事件处理异常');
                    }
                }
                $price = $orderDetail->total_price - min($orderDetail->total_price, $event->order_refund->reality_refund_price);
                (new AddShareOrder())->refund($orderDetail, $price);
            }
            $orderDetail->save();

            // 判断queue队列中的售后是否已经触发
            $queueId = CoreQueueData::select($event->order_refund->order->token);
            if ($queueId && !\Yii::$app->queue->isDone($queueId)) {
                // 若未触发
                return;
            } else {
                // 若已触发，则重新添加
                $id = \Yii::$app->queue->delay(0)->push(new OrderSalesJob([
                    'orderId' => $event->order_refund->order_id
                ]));
                CoreQueueData::add($id, $event->order_refund->order->token);
            }
        });
    }

    // 订单售后结束 自动结束订单
    private function finishOrder($orderRefundId)
    {
        try {
            \Yii::error('订单过售后自动结束订单开始');

            $orderRefund = OrderRefund::findOne($orderRefundId);
            if (!$orderRefund) {
                throw new \Exception('售后订单不存在' . $orderRefundId);
            }

            // 注意 订单需要重新查询
            $order = Order::findOne($orderRefund->order_id);
            if (!$order) {
                \Yii::error('订单不存在' . $orderRefund->order_id);
            }

            if ($order->is_confirm == 1) {
                $list = OrderRefund::find()->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'order_id' => $orderRefund->order->id
                ])
                    ->orderBy(['created_at' => SORT_ASC])
                    ->all();

                $newList = [];
                foreach ($list as $item) {
                    $newList[$item->order_detail_id][] = $item;
                }

                $num = count($order->detail);
                foreach ($newList as $item) {
                    if (count($item) == 1 && $item[0]->is_refund) {
                        $num = $num - 1;
                    }

                    // 一个订单详情 最多有两个售后订单
                    if (count($item) == 2 && ($item[1]->status == 3 || $item[1]->is_refund)) {
                        $num = $num - 1;
                    }
                }

                // 当订单详情全部 售后完成 过售后结束订单
                if (!$num) {
                    $order->is_sale = 1;
                    $res = $order->save();
                    if (!$res) {
                        throw new \Exception('保存异常');
                    }
                    \Yii::error('订单过售后自动结束订单结束');
                }
            }
        }catch(\Exception $exception) {
            \Yii::error('订单过售后自动结束订单异常');
            \Yii::error($exception->getMessage());
        }
    }

    /**
    * 订单售后结束 自动确认收货
    *
    */
    private function confirmOrder($orderRefundId)
    {
        try {
            \Yii::error('订单过售后自动确认收货开始');

            $orderRefund = OrderRefund::findOne($orderRefundId);
            if (!$orderRefund) {
                throw new \Exception('售后订单不存在' . $orderRefundId);
            }

            // 注意 订单需要重新查询
            $order = Order::findOne($orderRefund->order_id);
            if (!$order) {
                \Yii::error('订单不存在' . $orderRefund->order_id);
            }

            if ($order->is_confirm == 0) {
                $list = OrderRefund::find()->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'order_id' => $orderRefund->order->id
                ])
                    ->orderBy(['created_at' => SORT_ASC])
                    ->all();

                $newList = [];
                foreach ($list as $item) {
                    $newList[$item->order_detail_id][] = $item;
                }

                $num = count($order->detail);
                foreach ($newList as $item) {
                    if (count($item) == 1 && $item[0]->is_refund) {
                        $num = $num - 1;
                    }

                    // 一个订单详情 最多有两个售后订单
                    if (count($item) == 2 && ($item[1]->status == 3 || $item[1]->is_refund)) {
                        $num = $num - 1;
                    }
                }

                // 当订单详情售后全部完成 确认收货
                if (!$num) {
                    CommonOrder::getCommonOrder($order->sign)->confirm($order);
                    \Yii::error('订单过售后自动确认收货结束');
                }
            }
        }catch(\Exception $exception) {
            \Yii::error('订单过售后自动确认收货异常');
            \Yii::error($exception->getMessage());
        }
    }
}
