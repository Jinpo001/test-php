<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order;

use app\core\response\ApiCode;
use app\events\OrderRefundEvent;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\forms\common\template\order_pay_template\OrderRefundInfo;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\PaymentRefund;
use app\models\RefundAddress;
use app\models\User;
use yii\db\Exception;

class OrderRefundForm extends Model
{
    public $order_refund_id;
    public $merchant_remark;
    public $is_agree;
    public $address_id;
    public $type; //1退货 2 换货
    public $refund; //1退货 2 退款
    public $refund_price; //退款金额
    public $customer_name;
    public $express;
    public $express_no;
    public $is_express;
    public $mch_id;
    public $express_content;

    public function rules()
    {
        return [
            [['type', 'is_agree', 'order_refund_id'], 'required'],
            [['order_refund_id', 'address_id', 'is_agree', 'type', 'refund', 'is_express', 'mch_id'], 'integer'],
            [['refund_price'], 'number'],
            [['refund_price'], 'default', 'value' => 0],
            [['merchant_remark', 'express', 'express_no', 'customer_name', 'express_content'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $mchId = $this->mch_id ?: \Yii::$app->user->identity->mch_id;
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'id' => $this->order_refund_id,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => $mchId ?: 0,
                'is_delete' => 0,
            ])
                ->with('detail.goods')
                ->with('order')
                ->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            switch ($orderRefund->status) {
                case 1:
                    if ($this->is_agree) {
                        $res = $this->agree($orderRefund, '已同意售后申请');
                    } else {
                        $res = $this->refuse($orderRefund);
                    }
                    break;
                case 2:
                    if ($this->is_agree == 2) {
                        $res = $this->refuse($orderRefund);
                    } elseif ($orderRefund->type == 1 || $orderRefund->type == 3) {
                        $res = $this->refund($orderRefund);
                    } elseif ($orderRefund->type == 2) {
                        $res = $this->confirm($orderRefund);
                    }
                    break;
                case 3:
                    throw new \Exception('售后订单已拒绝，请勿重复操作');
                    break;
                default:
                    throw new \Exception('错误的售后订单');
            }
            if ($res['code'] == 0) {
                \Yii::$app->trigger(OrderRefund::EVENT_UPDATE_REFUND, new OrderRefundEvent([
                    'order_refund' => $orderRefund
                ]));
            }
            return $res;
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function sendTemplate($orderRefund, $refund_price, $remark)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(OrderRefundInfo::TPL_NAME)->send([
                'order_no' => $orderRefund->order->order_no,
                'name' => $orderRefund->detail->goods->name,
                'refundPrice' => $refund_price,
                'remark' => $remark,
                'user' => $orderRefund->order->user,
                'page' => 'pages/order/refund/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param OrderRefund $orderRefund
     * @return array
     * @throws \Exception
     * 拒绝售后申请
     */
    private function refuse($orderRefund)
    {
        $orderRefund->status = 3;
        $orderRefund->is_confirm = 1;
        $orderRefund->merchant_remark = $this->merchant_remark ? $this->merchant_remark : '卖家拒绝了您的售后申请';
        $orderRefund->status_time = mysql_timestamp();
        if (!$orderRefund->save()) {
            return $this->getErrorResponse($orderRefund);
        }
        $this->sendTemplate($orderRefund, '0.00', $orderRefund->merchant_remark);
        $this->sendSmsToUser($orderRefund, '0.00', '拒绝');
        \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent(['order_refund' => $orderRefund]));

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '处理成功,已拒绝售后申请',
        ];
    }

    /**
     * @param OrderRefund $orderRefund
     * @param $remark
     * @return array
     * 同意售后申请
     */
    private function agree($orderRefund, $remark)
    {
        try {
            // 仅退款申请
            if ($orderRefund->type == 3) {
                $orderRefund->is_confirm = 1;
                $orderRefund->is_send = 1;
            } else {
                // 退货退款申请
                if ($orderRefund->order->send_type != 1 && $orderRefund->order->send_type != 3 && $orderRefund->order->send_type != 2) {
                    $address = RefundAddress::findOne([
                        'mall_id' => \Yii::$app->mall->id,
                        'id' => $this->address_id,
                        'is_delete' => 0,
                    ]);
                    if (!$address) {
                        throw new \Exception('退货地址不能为空');
                    }
                    $orderRefund->address_id = $address->id;
                } else {
                    // 到店自提 同城配送 用户无需发货
                    $orderRefund->is_send = 1;
                }
            }

            $orderRefund->status = 2;
            $orderRefund->status_time = mysql_timestamp();
            //发送模版消息
            if (!$orderRefund->save()) {
                throw new Exception($this->getErrorMsg($orderRefund));
            }

            // 货到付款方式售后处理
            $res = (new OrderRefund())->updatePayStatus($orderRefund);

            //通知
            $this->sendTemplate($orderRefund, $orderRefund->refund_price, $remark);
            $this->sendSmsToUser($orderRefund, $orderRefund->refund_price, '通过');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功,已同意售后申请',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param OrderRefund $orderRefund
     * @return array
     * 确认退款
     */
    private function refund($orderRefund)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($orderRefund->is_confirm == 0) {
                throw new \Exception('售后订单未确认收货');
            }

            if ($orderRefund->is_refund == 1) {
                throw new \Exception('该订单已退款');
            }

            // 兼容 更新之前的订单 is_refund 是2 但是有可能没有退款
            if ($orderRefund->is_refund == 2) {
                /** @var PaymentOrder $paymentOrder */
                $paymentOrder = PaymentOrder::find()->where(['order_no' => $orderRefund->order->order_no])->with('paymentOrderUnion')->one();
                $paymentRefund = PaymentRefund::find()->where(['out_trade_no' => $paymentOrder->paymentOrderUnion->order_no])->one();
                if ($paymentRefund) {
                    throw new \Exception('售后订单已打款！无需重复');
                } else {
                    if ($orderRefund->is_refund == 1) {
                        throw new \Exception('售后订单已打款！无需重复');
                    }
                }
            }

            $orderRefund->is_refund = 1;
            $orderRefund->refund_time = mysql_timestamp();
            $user = User::findOne(['id' => $orderRefund->order->user_id]);
            // 用户抵扣积分恢复
            $goodsInfo = \Yii::$app->serializer->decode($orderRefund->detail->goods_info);
            $goodsAttr = $goodsInfo->goods_attr;
            if ($goodsAttr['use_integral']) {
                $desc = '商品订单退款，订单' . $orderRefund->order->order_no;
                \Yii::$app->currency->setUser($user)->integral->refund(
                    (int) $goodsAttr['use_integral'],
                    $desc
                );
            }
            if ($this->refund_price < 0) {
                throw new \Exception('退款金额不能小于0');
            }
            //卖家自定义退款金额
            if ($this->refund_price) {
                $orderRefund->reality_refund_price = $this->refund_price;
            }
            if (!$orderRefund->save()) {
                throw new \Exception($this->getErrorMsg($orderRefund));
            }
            // 退款
            $advance_refund = 0;
            $price = 0;
            if ($orderRefund->order->pay_type == 2) {
                // 货到付款订单退款，线下沟通
                $msg = '订单为货到付款方式，退款金额请线下自行处理';
                //预售退款涉及退定金
                if ($orderRefund->reality_refund_price > 0) {
                    //预售订单售后退款，退款金额包含定金，判断中需要扣除
                    $order_info = Order::findOne(['order_no' => $orderRefund->order->order_no, 'sign' => 'advance']);
                    $price = $orderRefund->reality_refund_price;
                    if (!empty($order_info)) {
                        //判断是否存在插件，是否有插件权限
                        $bool = false;
                        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo); //取商城所属账户权限
                        if (!is_array($permission_arr) && $permission_arr) {
                            $bool = true;
                        } else {
                            foreach ($permission_arr as $value) {
                                if ($value == 'advance') {
                                    $bool = true;
                                    break;
                                }
                            }
                        }
                        if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                            \Yii::info('预售货到付款退款只退定金');
                            if ($price > $orderRefund->order->total_price) {
//退款金额大于尾款金额
                                $advance_refund = $price - $orderRefund->order->total_price;
                                $price = $orderRefund->order->total_price; //总退款大于商品总价，取商品总价
                            }
                        }
                    }
                }
            } else {
                if ($orderRefund->reality_refund_price > 0) {
                    //预售订单售后退款，退款金额包含定金，判断中需要扣除
                    $order_info = Order::findOne(['order_no' => $orderRefund->order->order_no, 'sign' => 'advance']);
                    $price = $orderRefund->reality_refund_price;
                    if (!empty($order_info)) {
                        //判断是否存在插件，是否有插件权限
                        $bool = false;
                        $permission_arr = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo); //取商城所属账户权限
                        if (!is_array($permission_arr) && $permission_arr) {
                            $bool = true;
                        } else {
                            foreach ($permission_arr as $value) {
                                if ($value == 'advance') {
                                    $bool = true;
                                    break;
                                }
                            }
                        }
                        if (\Yii::$app->plugin->getInstalledPlugin('advance') && $bool) {
                            $paymentOrder = \app\models\PaymentOrder::findOne([
                                'order_no' => $orderRefund->order->order_no,
                                'is_pay' => 1,
                            ]);
                            if (price_format($paymentOrder->amount - $paymentOrder->refund) < price_format($orderRefund->refund_price)) {
                                \Yii::info('预售退款涉及到定金');
                                $advance_refund = $price - ($paymentOrder->amount - $paymentOrder->refund);
                                $price = price_format($paymentOrder->amount - $paymentOrder->refund);
                            }
                        }
                    }
                }
                $msg = '处理成功，已完成退款';
            }

            \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent([
                'order_refund' => $orderRefund,
                'advance_refund' => price_format($advance_refund > 0 ? $advance_refund : 0),
            ]));

            \Yii::$app->payment->refund($orderRefund->order->order_no, $price);
            // 退款成功发送模版消息
            $this->sendTemplate($orderRefund, $price, '退款已完成');
            $this->sendSmsToUser($orderRefund, $price, '通过');

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $msg,
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine(),
                ],
            ];
        }
    }

    /**
     * @param OrderRefund $orderRefund
     * @return array
     * @throws \Exception
     * 换货确认
     */
    private function confirm($orderRefund)
    {
        //if (substr_count($this->express, '京东') && empty($this->customer_name)) {
        //    throw new \Exception('京东物流必须填写京东商家编码');
        // }
        // 用户已发货|商家确认收货
        $orderRefund->is_confirm = 1;
        $orderRefund->confirm_time = mysql_timestamp();

        // 换货-确认收货需填写快递单号
        if ($this->is_express == 1) {
            (new Order())->validateExpress($this->express);

            if (!$this->express_no) {
                throw new \Exception('请填写快递单号');
            }

            $orderRefund->merchant_customer_name = $this->customer_name;
            $orderRefund->merchant_express = $this->express;
            $orderRefund->merchant_express_no = $this->express_no;
        } else {
            $orderRefund->merchant_express_content = $this->express_content ?: '';
        }
        $orderRefund->merchant_remark = $this->merchant_remark ?: '';
        $res = $orderRefund->save();
        if (!$res) {
            throw new \Exception($this->getErrorMsg($orderRefund));
        }
        \Yii::$app->trigger(OrderRefund::EVENT_REFUND, new OrderRefundEvent([
            'order_refund' => $orderRefund,
        ]));

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '确认收货成功',
        ];
    }

    /**
     * @param OrderRefund $orderRefund
     * @param $refund_price
     * @param $remark
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($orderRefund, $refund_price, $remark)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            $order = $orderRefund->order;
            if (!$order->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $order->user;
            $messageService->content = [
                'mch_id' => $order->mch_id,
                'args' => [$remark]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($order->user);
            $messageService->tplKey = OrderRefundInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
    }
}
