<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\payment\Payment;
use app\core\response\ApiCode;
use app\events\OrderEvent;
use app\forms\common\coupon\CommonCouponList;
use app\helpers\ArrayHelper;
use app\models\FullReduceActivity;
use app\models\MallMembers;
use app\models\Model;
use app\models\Order;
use app\models\PaymentOrderUnion;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\teller\Plugin;
use app\plugins\teller\forms\OrderQueryForm;
use app\plugins\teller\forms\web\TellerRefundForm;
use app\plugins\teller\forms\web\order\TellerOrderSubmitForm;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\vip_card\forms\common\CommonVip;

class TellerOrderForm extends Model
{
    public $keyword;

    public $order_id;
    public $payment_order_union_id;
    public $seller_remark;
    public $pay_type;

    public function rules()
    {
        return [
            [['order_id', 'payment_order_union_id'], 'integer'],
            [['keyword', 'seller_remark', 'pay_type'], 'string'],
            [['keyword', 'pay_type'], 'trim'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $cashier = TellerCashier::findOne(['user_id' => \Yii::$app->user->id]);

            $orderQuery = Order::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'is_pay' => 1,
                'is_confirm' => 1,
                'status' => 1,
                'cancel_status' => 0,
                'store_id' => $cashier->store_id,
                'sign' => (new Plugin())->getName()
            ]);

            if ($this->keyword) {
                $orderQuery->andWhere(['like', 'order_no', $this->keyword]);
            }

            $orderIds = $orderQuery->select('id');

            $query = TellerOrders::find()->andWhere([
                'order_type' => TellerOrders::ORDER_TYPE_ORDER,
                'order_id' => $orderIds
            ])->with(['order' => function($query) {
                $query->select(['id', 'order_no', 'total_pay_price', 'seller_remark']);
            }, 'sales' => function($query) {
                $query->select(['id', 'name']);
            }, 'cashier.user' => function($query) {
                $query->select(['id', 'nickname']);
            }, 'order.detail' => function($query) {
                $query->select(['id', 'order_id', 'goods_id', 'num', 'goods_info', 'total_price']);
            }, 'order.detail.refund' => function($query) {
                $query->select(['id', 'order_detail_id']);
            }]);

            $list = $query->select(['id', 'order_id', 'sales_id', 'cashier_id', 'created_at'])
                ->orderBy(['created_at' => SORT_DESC])
                ->page($pagination)
                ->all();

            $newList = [];
            foreach ($list as $item) {
                $newItem = [];
                $newItem['order_id'] = $item->order->id;
                $newItem['order_no'] = $item->order->order_no;
                $newItem['seller_remark'] = $item->order->seller_remark;
                $newItem['total_pay_price'] = $item->order->total_pay_price;
                $newItem['sales_name'] = $item->sales ? $item->sales->name : '';
                $newItem['cashier_name'] = $item->cashier->user->nickname;
                $newDetailList = [];
                $goodsCount = 0;
                foreach ($item->order->detail as $orderDetail) {
                    $goodsCount += $orderDetail->num;
                    $newDetailItem = [];
                    $newDetailItem['id'] = $orderDetail->id;
                    $newDetailItem['num'] = $orderDetail->num;
                    $newDetailItem['total_price'] = $orderDetail->total_price;
                    $goodsInfo = json_decode($orderDetail->goods_info, true);
                    $newDetailItem['name'] = $goodsInfo['goods_attr']['name'];
                    $newDetailItem['cover_pic'] = $goodsInfo['goods_attr']['cover_pic'];
                    $newDetailItem['refund_order_id'] = $orderDetail->refund ? $orderDetail->refund->id : 0;
                    $newDetailList[] = $newDetailItem;
                }
                $newItem['goods_count'] = $goodsCount;
                $newItem['detail'] = $newDetailList;
                $newList[] = $newItem;
            }
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination,
                ],
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function orderShow()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $query = TellerOrders::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'order_type' => TellerOrders::ORDER_TYPE_ORDER,
                'order_id' => $this->order_id
            ])->with(['order' => function($query) {
                $query->select(['id', 'order_no', 'total_pay_price', 'seller_remark', 'is_sale']);
            }, 'sales' => function($query) {
                $query->select(['id', 'name']);
            }, 'cashier.user' => function($query) {
                $query->select(['id', 'nickname']);
            }, 'order.detail' => function($query) {
                $query->select(['id', 'order_id', 'goods_id', 'num', 'goods_info', 'total_price']);
            }, 'order.detail.orderRefund', 'order.paymentOrder']);

            $detail = $query->select(['id', 'order_id', 'sales_id', 'cashier_id', 'pay_type'])->one();

            if (!$detail) {
                throw new \Exception('订单不存在');
            }

            $newDetail = [];
            $newDetail['order_id'] = $detail->order->id;
            $newDetail['order_no'] = $detail->order->order_no;
            $newDetail['seller_remark'] = $detail->order->seller_remark;
            $newDetail['total_pay_price'] = $detail->order->total_pay_price;
            $newDetail['sales_name'] = $detail->sales ? $detail->sales->name : '';
            $newDetail['cashier_name'] = $detail->cashier->user->nickname;
            $newDetailList = [];
            $goodsCount = 0;
            foreach ($detail->order->detail as $orderDetail) {
                $goodsCount += $orderDetail->num;
                $newDetailItem = [];
                $newDetailItem['id'] = $orderDetail->id;
                $newDetailItem['goods_id'] = $orderDetail->goods_id;
                $newDetailItem['num'] = $orderDetail->num;
                $newDetailItem['total_price'] = $orderDetail->total_price;
                $goodsInfo = json_decode($orderDetail->goods_info, true);
                $newDetailItem['name'] = $goodsInfo['goods_attr']['name'];
                $newDetailItem['cover_pic'] = $goodsInfo['goods_attr']['cover_pic'];
                $newDetailItem['is_show_sale'] = $detail->order->is_sale == 1 ? 0 : 1;
                $newDetailItem['refund'] = null;

                if ($orderDetail->orderRefund) {
                    $orderRefund = $orderDetail->orderRefund;
                    $refundData = [
                        'refund_id' => $orderRefund->id,
                        'refund_type' => $orderRefund->type == 1 ? '退货退款' : '仅退款',
                        'refund_way' => $detail->getPayWay($detail->order->paymentOrder->pay_type),
                        'remark' => $orderRefund->remark,
                        'refund_price' => $orderRefund->reality_refund_price,
                        'status_text' => $orderRefund->statusText($orderRefund),
                    ];
                    $newDetailItem['refund'] = $refundData;
                }

                $attrList = [];
                foreach ($goodsInfo['attr_list'] as $item) {
                    $attrItem = [];
                    $attrItem = sprintf('%s:%s', $item['attr_group_name'], $item['attr_name']);
                    $attrList[] = $attrItem;
                }

                $newDetailItem['attr'] = $attrList;
                $newDetailList[] = $newDetailItem;
            }
            $newDetail['goods_count'] = $goodsCount;
            $newDetail['detail'] = $newDetailList;

            $newDetail['pay_type'] = $detail->getPayWay($detail->pay_type);
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $newDetail,
                ],
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function sellerRemark()
    {
        try {
            $order = Order::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->order_id,
                'sign' => (new Plugin())->getName()
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            $order->seller_remark = $this->seller_remark;
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];

        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line'=> $exception->getLine()
            ];
        }
    }

    public function orderCancel()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transactioan = \Yii::$app->db->beginTransaction();
        try {
            $paymentOrderUnion = PaymentOrderUnion::find()->andWhere([
                'id' => $this->payment_order_union_id
            ])
                ->with('paymentOrder.order')
                ->one();

            if (!$paymentOrderUnion) {
                throw new \Exception('待支付订单不存在。');
            }

            foreach ($paymentOrderUnion->paymentOrder as $paymentOrder) {
                $order = Order::findOne([
                    'order_no' => $paymentOrder->order_no,
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'status' => 0,
                    'is_delete' => 1,// 收银台订单创建订单时该状态为1 支付成功改为0
                ]);

                if (!$order) {
                    throw new \Exception('订单不存在');
                }

                if ($order->cancel_status == 1) {
                    throw new \Exception('订单取消');
                }

                $order->words = '收银台订单取消';
                $order->cancel_status = 1;
                $order->cancel_time = mysql_timestamp();

                $order->status = 1;
                $order->is_delete = 0; // TODO 是否要改

                if (!$order->save()) {
                    throw new \Exception($this->getErrorMsg($order));
                }

                $this->reverseOrder($paymentOrderUnion, $this->pay_type);

                \Yii::$app->trigger(Order::EVENT_CANCELED, new OrderEvent([
                    'order' => $order,
                    'action_type' => 5
                ]));
            }
            
            $transactioan->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '取消成功'
            ];
        } catch (\Exception $e) {
            $transactioan->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line'=>$e->getLine(),
                'e'=>$e->getTraceAsString()
            ];
        }
    }

    // 撤销订单 撤销后的订单不可再支付
    private function reverseOrder($paymentOrderUnion, $payType)
    {
        \Yii::warning('撤销订单开始');

        $payTypeList = [Payment::PAY_TYPE_WECHAT_SCAN, Payment::PAY_TYPE_ALIPAY_SCAN];
        if (!in_array($payType, $payTypeList)) {
            throw new \Exception('支付方式异常' . json_encode($payTypeList));
        }

        try {
            if (!$paymentOrderUnion) {
                throw new \Exception('PaymentOrderUnion 订单不存在');
            }

            $orderQueryForm = new OrderQueryForm();
            $orderQueryForm->id = $paymentOrderUnion->id;
            $orderQueryForm->pay_type = $payType;
            $orderQueryForm->reverse();

            \Yii::warning('撤销订单结束');
        }catch(\Exception $exception) {
            \Yii::warning('撤销订单异常');
            \Yii::warning($exception);
        }
    }
}
