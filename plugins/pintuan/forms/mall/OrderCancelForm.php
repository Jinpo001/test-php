<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\forms\mall;


use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\forms\common\ecard\CommonEcard;
use app\forms\common\template\TemplateList;
use app\models\GoodsAttr;
use app\models\Model;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\User;
use app\plugins\pintuan\forms\common\v2\PintuanFailInfo;
use app\plugins\pintuan\models\Order;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;

class OrderCancelForm extends Model
{
    public $order_id;
    public $pintuan_order_id;

    public function rules()
    {
        return [
            [['order_id', 'pintuan_order_id'], 'required'],
            [['order_id', 'pintuan_order_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var PintuanOrders $pintuanOrder */
            $order = Order::find()->andWhere(['id' => $this->order_id, 'is_delete' => 0])->one();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $pintuanOrderRelation = PintuanOrderRelation::find()->andWhere(['order_id' => $order->id, 'is_delete' => 0])->one();
            if (!$pintuanOrderRelation) {
                throw new \Exception('拼团订单不存在');
            }

            if ($pintuanOrderRelation->is_refund == 1) {
                throw new \Exception('拼团订单已退款');
            }

            $this->sendBack($pintuanOrderRelation);

            $pintuanOrderRelation->is_refund = 1;
            $res = $pintuanOrderRelation->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($pintuanOrderRelation));
            }

            $pintuanOrderRelation->order->cancel_status = 1;
            $pintuanOrderRelation->order->cancel_time = mysql_timestamp();
            $pintuanOrderRelation->order->seller_remark = '拼团失败,订单状态更新为取消';
            $pintuanOrderRelation->order->status = 1;
            $res = $pintuanOrderRelation->order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($pintuanOrderRelation->order));
            }

            $this->refund($pintuanOrderRelation);

            $transaction->commit();

            $count = PintuanOrderRelation::find()
                ->andWhere(['pintuan_order_id' => $pintuanOrderRelation->pintuan_order_id])
                ->andWhere(['!=', 'is_refund', 1])
                ->count();
            if ($count == 0) {
                $pintuanOrderRelation->pintuanOrder->status = 3;
                $pintuanOrderRelation->pintuanOrder->save();
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '退款成功'
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

    private function sendBack($pintuanOrderRelation)
    {
        // is_refund == 0 表示该订单所有使用的优惠都没有退还给用户
        if ($pintuanOrderRelation->is_refund == 0) {
            $commonEcard = CommonEcard::getCommon();
            // 如果是机器人则跳过
            if ($pintuanOrderRelation->robot_id > 0) {
                throw new \Exception('机器人无需退款');
            }

            if (!$pintuanOrderRelation->user) {
                throw new \Exception('用户不存在');
            }
            // 判断订单是否取消，为防止订单积分、优惠券、余额重复退
            if ($pintuanOrderRelation->order->cancel_status == 1 || $pintuanOrderRelation->order->is_delete == 1) {
                throw new \Exception('拼团订单已取消或已删除');
            }
            // 拼团不成功的返还卡密
            $commonEcard->refundEcard([
                'type' => 'order',
                'order' => $pintuanOrderRelation->order,
            ]);

            // 用户积分恢复
            if ($pintuanOrderRelation->order->use_integral_num) {
                $desc = '商品订单取消，订单号' . $pintuanOrderRelation->order->order_no;
                $customDesc = \Yii::$app->serializer->encode($pintuanOrderRelation->order);
                \Yii::$app->currency->setUser($pintuanOrderRelation->user)->integral->add(
                    (int) $pintuanOrderRelation->order->use_integral_num,
                    $desc,
                    $customDesc,
                    $pintuanOrderRelation->order->order_no
                );
            }

            // 优惠券恢复
            if ($pintuanOrderRelation->order->use_user_coupon_id) {
                UserCoupon::updateAll(['is_use' => 0], ['id' => $pintuanOrderRelation->order->use_user_coupon_id]);
            }

            // 库存退回
            /** @var OrderDetail $dItem */
            foreach ($pintuanOrderRelation->order->detail as $dItem) {
                $goodsInfo = \Yii::$app->serializer->decode($dItem->goods_info);
                $goodsAttr = GoodsAttr::findOne(['goods_id' => $dItem->goods_id, 'id' => $goodsInfo->goods_attr['id']]);
                $goodsAttr->stock += $dItem->num;
                if (!$goodsAttr->save()) {
                    throw new \Exception((new Model())->getErrorMsg($goodsAttr));
                }
            }

            $pintuanOrderRelation->order->cancel_status = 1;
            $pintuanOrderRelation->order->cancel_time = mysql_timestamp();
            $pintuanOrderRelation->order->seller_remark = '拼团失败,订单状态更新为取消';
            $pintuanOrderRelation->order->status = 1;
            $res = $pintuanOrderRelation->order->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($pintuanOrderRelation->order));
            }
        }
    }

    /**
     * 拼团失败订阅消息
     * @param PintuanOrderRelation $item
     * @throws \Exception
     */
    private function sendTemplateMsg($item)
    {
        try {

            $user = User::findOne($item->user_id);
            if (!$user) {
                throw new \Exception('用户不存在！,拼团失败订阅消息发送失败');
            }

            $goodsName = '';
            /** @var OrderDetail $dItem */
            foreach ($item->order->detail as $dItem) {
                $goodsName .= $dItem->goods->getName();
            }

            TemplateList::getInstance()->getTemplateClass(PintuanFailInfo::TPL_NAME)->send([
                'order_no' => $item->order->order_no,
                'goodsName' => $goodsName,
                'remark' => '拼团人数不足',
                'user' => $user,
                'page' => 'plugins/pt/detail/detail?id=' . $item->pintuan_order_id
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * 拼团订单失败退款 退款要放到最后处理 因为退款无法回滚
     * @param  [type] $item [description]
     * @return [type]       [description]
     */
    private function refund($item)
    {
        if ($item->order->is_recycle == 1) {
            \Yii::warning('订单加入回收站，无需退款');
            return false;
        }

        $paymentOrder = PaymentOrder::find()->where(['order_no' => $item->order->order_no])->with('paymentOrderUnion')->one();
        $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
        // 订单已退款
        if ($paymentRefund) {
            \Yii::warning('订单ID:' . $item->order->id . '已退过款');
            return false;
        }

        if ($item->order->is_pay == 1) {
            // 已付款就退款
            $res = \Yii::$app->payment->refund($item->order->order_no, $item->order->total_pay_price);
            $this->sendTemplateMsg($item);
        }

        \Yii::warning('拼团自动退款执行完成');
        return true;
    }
}