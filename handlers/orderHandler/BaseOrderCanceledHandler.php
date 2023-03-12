<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 17:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;

use app\events\OrderEvent;
use app\forms\common\goods\CommonGoods;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\forms\common\template\order_pay_template\OrderCancelInfo;
use app\jobs\ChangeShareOrderJob;
use app\models\GoodsAttr;
use app\models\Order;
use app\models\OrderDetail;
use app\models\ShareOrder;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use yii\db\Exception;

/**
 * @property User $user
 */
abstract class BaseOrderCanceledHandler extends BaseOrderHandler
{
    public $user;

    public function handle()
    {
        return $this->cancel();
    }

    protected function cancel()
    {
        \Yii::$app->setMchId($this->event->order->mch_id);
        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var OrderEvent $event */
            $this->action();
            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::error('订单取消完成事件：');
            \Yii::error($exception);
            throw $exception;
        }
    }

    protected function action()
    {
        $this->integralResume()->couponResume()->refund()->cardResume()
            ->shareResume()->sendTemplate()->updateGoodsInfo()->goodsAddStock($this->event->order)->sendSmsToUser();
    }

    /**
     * 用户积分恢复
     */
    protected function integralResume()
    {
        $user = User::findOne(['id' => $this->event->order->user_id]);
        if ($this->event->order->use_integral_num) {
            $desc = '商品订单取消，订单' . $this->event->order->order_no;
            \Yii::$app->currency->setUser($user)->integral
                ->refund((int)$this->event->order->use_integral_num, $desc);
        }
        return $this;
    }

    protected function couponResume()
    {
        // 优惠券恢复
        if ($this->event->order->use_user_coupon_id) {
            $userCoupon = UserCoupon::findOne(['id' => $this->event->order->use_user_coupon_id]);
            $userCoupon->is_use = 0;
            $userCoupon->save();
        }

        return $this;
    }

    protected function refund()
    {
        // 已付款就退款
        if ($this->event->order->is_pay == 1) {
            \Yii::$app->payment->refund($this->event->order->order_no, $this->event->order->total_pay_price);
        }
        return $this;
    }

    protected function cardResume()
    {
        /** @var UserCard[] $userCards */
        // 销毁发放的卡券
        $userCards = UserCard::find()->with('card')->where(['order_id' => $this->event->order->id])->all();
        foreach ($userCards as $userCard) {
            $userCard->is_delete = 1;
            $userCard->card->updateCount('add', 1);
            $res = $userCard->save();
            if (!$res) {
                \Yii::error('卡券销毁事件处理异常');
            }
        }
        return $this;
    }

    protected function shareResume()
    {
        ShareOrder::updateAll(['is_refund' => 1], ['order_id' => $this->event->order->id]);
        \Yii::$app->queue->delay(0)->push(new ChangeShareOrderJob([
            'mall' => \Yii::$app->mall,
            'order' => $this->event->order,
            'type' => 'sub',
            'before' => []
        ]));
        return $this;
    }

    protected function sendTemplate()
    {
        try {
            $order = $this->event->order;
            $remark = $order->cancel_status == 1 ? '商家同意取消' : '商家拒绝取消';

            $goodsName = '';
            foreach ($order->detail as $orderDetail) {
                $goodsName .= $orderDetail->goods->name;
            }

            TemplateList::getInstance()->getTemplateClass(OrderCancelInfo::TPL_NAME)->send([
                'goodsName' => $goodsName,
                'order_no' => $order->order_no,
                'price' => $order->total_pay_price,
                'remark' => $remark,
                'user' => $order->user,
                'page' => 'pages/order/index/index?status=2'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }

        return $this;
    }

    protected function updateGoodsInfo()
    {
        // 修改商品支付信息
        CommonGoods::getCommon()->setGoodsPayment($this->event->order, 'sub');
        CommonGoods::getCommon()->setGoodsSales($this->event->order);

        return $this;
    }

    /**
     * @param Order $order
     * @throws Exception
     */
    protected function goodsAddStock($order)
    {
        /* @var OrderDetail[] $orderDetail */
        $orderDetail = $order->detail;
        $goodsAttrIdList = [];
        $goodsNum = [];
        foreach ($orderDetail as $item) {
            $goodsInfo = \Yii::$app->serializer->decode($item->goods_info);
            $goodsAttrIdList[] = $goodsInfo['goods_attr']['id'];
            $goodsNum[$goodsInfo['goods_attr']['id']] = $item->num;
        }
        $goodsAttrList = GoodsAttr::find()->where(['id' => $goodsAttrIdList])->all();
        /* @var GoodsAttr[] $goodsAttrList */
        foreach ($goodsAttrList as $goodsAttr) {
            $goodsAttr->updateStock($goodsNum[$goodsAttr->id], 'add');
        }

        return $this;
    }

    protected function sendSmsToUser()
    {
        try {
            \Yii::warning('----消息发送提醒----');
            $order = $this->event->order;
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
            $messageService->tplKey = OrderCancelInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
