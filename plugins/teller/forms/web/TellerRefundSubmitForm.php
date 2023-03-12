<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\events\OrderRefundEvent;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerWorkLog;


class TellerRefundSubmitForm extends Model
{
	public $order_detail_id;
    public $type;
    public $refund_price;
    public $remark;

    public function rules()
    {
        return [
            [['order_detail_id', 'type', 'remark', 'refund_price'], 'required'],
            [['order_detail_id', 'type'], 'integer'],
            [['remark', 'refund_price'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_detail_id' => '订单详情ID',
            'type' => '退款类型',
            'remark' => '备注',
            'refund_price' => '退款金额',
        ];
    }

	public function submit()
	{
		if (!$this->validate()) {
            return $this->getErrorResponse();
        }

		$transaction = \Yii::$app->db->beginTransaction();
		try {
            $this->checkIsRefund();
			$this->checkData();

			$orderDetail = OrderDetail::find()->where([
                'id' => $this->order_detail_id,
            ])->with(['order', 'userCards' => function ($query) {
                $query->andWhere(
                    [
                        'or',
                        ['>', 'use_number', 0],
                        ['is_use' => 1],
                        ['>', 'receive_id', 0]
                    ]
                );
            }])->one();

            if (!$orderDetail) {
                throw new \Exception('订单不存在');
            }
            if ($orderDetail->order->is_sale == 1) {
                throw new \Exception('订单已过售后时间,无法申请售后');
            }

			// 退款金额不能大于商品单价
            if (price_format($this->refund_price) > price_format($orderDetail->total_price)) {
                throw new \Exception('最多可退款金额￥' . price_format($orderDetail->total_price));
            }

            if (count($orderDetail->userCards) > 0) {
                throw new \Exception('商品赠送的卡券已使用,该商品无法申请退货');
            }

            // 生成售后订单
            $orderRefund = new OrderRefund();
            $orderRefund->mall_id = \Yii::$app->mall->id;
            $orderRefund->mch_id = $orderDetail->order->mch_id;
            $orderRefund->user_id = $orderDetail->order->user_id;
            $orderRefund->order_id = $orderDetail->order_id;
            $orderRefund->order_detail_id = $this->order_detail_id;
            $orderRefund->order_no = Order::getOrderNo('RE');
            $orderRefund->type = $this->type;
            $orderRefund->refund_price = $this->refund_price;
            $orderRefund->reality_refund_price = $this->refund_price;
            $orderRefund->status = 2;
            $orderRefund->status_time = date('Y-m-d H:i:s', time());
            $orderRefund->is_confirm = 1;
            $orderRefund->confirm_time = date('Y-m-d H:i:s', time());
            $orderRefund->is_refund = 1;
            $orderRefund->refund_time = date('Y-m-d H:i:s', time());
            $orderRefund->is_send = 1;
            $orderRefund->send_time = date('Y-m-d H:i:s', time());
            $orderRefund->remark = $this->remark;
            $orderRefund->pic_list = json_encode([]);
            $orderRefund->mobile = '';
            $orderRefund->refund_data = json_encode([]);
            $res = $orderRefund->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($orderRefund));
            }

            // 更新订单详情售后状态
            $orderDetail->refund_status = 2;
            if (!$orderDetail->save()) {
                throw new \Exception($this->getErrorMsg($orderDetail));
            }

            // 更新订单售后状态
            if ($orderDetail->order->sale_status == 0) {
                $orderDetail->order->sale_status = 1;
                if (!$orderDetail->order->save()) {
                    throw new \Exception($this->getErrorMsg($orderDetail->order));
                }
            }

            $tellerOrder = TellerOrders::findOne(['order_id' => $orderRefund->order_id]);
            if (!$tellerOrder) {
                throw new \Exception('收银台订单不存在');
            }
            $tellerOrder->is_refund = 1;
            $tellerOrder->refund_money = $tellerOrder->refund_money + $this->refund_price;
            $res = $tellerOrder->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($tellerOrder));
            }

            $this->setWorkLog($tellerOrder);

            $this->refund($orderRefund, $this->refund_price);

			$transaction->commit();

			return [
				'code' => ApiCode::CODE_SUCCESS,
				'msg' => '处理成功，已完成退款'
			];
		}catch(\Exception $exception) {
			$transaction->rollBack();
            return [
                'code'  => ApiCode::CODE_ERROR,
                'msg'   => $exception->getMessage(),
                'line' => $exception->getLine(),
            ];
		}
	}

    // 售后订单统计
    private function setWorkLog($tellerOrder)
    {
        \Yii::warning('收银台售后订单统计开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $workLog = $tellerOrder->workLog;
            if (!$workLog) {
                throw new \Exception('无交班记录');
            }

            $tellerOrder->is_statistics = 1;
            $res = $tellerOrder->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($tellerOrder));
            }

            $extra = json_decode($workLog->extra_attributes, true);
            $refundPrice = $this->refund_price;
            $extra['refund']['total_refund'] = price_format($extra['refund']['total_refund'] + $refundPrice);
            $extra['refund']['total_order'] += 1;
            switch ($tellerOrder->order->paymentOrder->pay_type) {
                case 3:
                    $extra['refund']['balance_refund'] = price_format($extra['refund']['balance_refund'] + $refundPrice);
                    break;
                case 9:
                    $extra['refund']['cash_refund'] = price_format($extra['refund']['cash_refund'] + $refundPrice);
                    break;
                case 10:
                    $extra['refund']['pos_refund'] = price_format($extra['refund']['pos_refund'] + $refundPrice);
                    break;
                case 11:
                    $extra['refund']['wechat_refund'] = price_format($extra['refund']['wechat_refund'] + $refundPrice);
                    break;
                case 12:
                    $extra['refund']['alipay_refund'] = price_format($extra['refund']['alipay_refund'] + $refundPrice);
                    break;
                default:
                    throw new \Exception('统计未知支付类型' . $tellerOrder->order->paymentOrder->pay_type);
                    break;
            }
            $workLog->extra_attributes = json_encode($extra);
            $workLog->save();

            $transaction->commit();
            \Yii::warning('收银台售后订单统计结束');
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('收银台售后订单统计异常');
            \Yii::error($exception);
        }
    }

	private function refund($orderRefund, $refundPrice)
	{
		$user = User::findOne(['id' => $orderRefund->order->user_id]);
        // 用户抵扣积分恢复
        $goodsInfo = \Yii::$app->serializer->decode($orderRefund->detail->goods_info);
        $goodsAttr = $goodsInfo->goods_attr;
        if ($goodsAttr['use_integral']) {
            $desc = '商品订单退款，订单' . $orderRefund->order->order_no;
            \Yii::$app->currency->setUser($user)->integral->refund(
                (int)$goodsAttr['use_integral'],
                $desc
            );
        }

        \Yii::$app->payment->refund($orderRefund->order->order_no, $refundPrice);

        \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent([
            'order_refund' => $orderRefund,
            'advance_refund' => price_format(0),
        ]));
	}

	private function checkData()
    {
        if (mb_strlen($this->remark) > 200) {
            throw new \Exception("备注最多输入200个字");
        }

        if ($this->refund_price < 0) {
            throw new \Exception('退款金额不能小于0');
        }

        if (!in_array($this->type, [1,3])) {
        	throw new \Exception('退款类型不支持');
        }
    }

    /**
     *  检测该订单商品是否已经售后过.
     */
    private function checkIsRefund()
    {
        $orderRefund = OrderRefund::find()->where([
            'mall_id'         => \Yii::$app->mall->id,
            'order_detail_id' => $this->order_detail_id,
            'is_delete'       => 0,
        ])->one();

        if ($orderRefund) {
            throw new \Exception('该订单已生成售后订单,无需重复申请');
        }
    }
}
