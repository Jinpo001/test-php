<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\api\coupon;

use app\core\payment\PaymentException;
use app\core\payment\PaymentOrder;
use app\forms\api\order\OrderPayFormBase;
use app\models\OrderSubmitResult;
use app\plugins\integral_mall\forms\common\SettingForm;
use app\plugins\integral_mall\models\IntegralMallCouponOrderSubmitResult;
use app\plugins\integral_mall\models\IntegralMallCouponsOrders;
use app\plugins\integral_mall\models\IntegralMallSetting;

class CouponOrderPayForm extends OrderPayFormBase
{
    public $queue_id;
    public $token;

    public function rules()
    {
        return [
            [['queue_id', 'token'], 'required'],
        ];
    }

    public function getResponseData()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse($this);
        }

        if (!\Yii::$app->queue->isDone($this->queue_id)) {
            return [
                'code' => 0,
                'data' => [
                    'retry' => 1,
                ],
            ];
        }
        $orders = IntegralMallCouponsOrders::find()->where([
            'token' => $this->token,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
        ])->all();
        if (!$orders || !count($orders)) {
            $orderSubmitResult = IntegralMallCouponOrderSubmitResult::findOne([
                'token' => $this->token,
            ]);
            if ($orderSubmitResult) {
                return [
                    'code' => 1,
                    'msg' => $orderSubmitResult->data,
                ];
            }
            return [
                'code' => 1,
                'msg' => '订单不存在或已失效。',
            ];
        }
        return $this->getReturnData($orders);
    }

    protected function getReturnData($orders)
    {
        $setting = (new SettingForm())->search();
        $paymentType = [];
        foreach ($setting['payment_type'] as $item) {
            if ($item == 'balance') {
                $paymentType[] = \app\core\payment\Payment::PAY_TYPE_BALANCE;
            }
            if ($item == 'online_pay') {
                $paymentType = array_merge($paymentType, [
                    \app\core\payment\Payment::PAY_TYPE_WECHAT,
                    \app\core\payment\Payment::PAY_TYPE_ALIPAY,
                    \app\core\payment\Payment::PAY_TYPE_BAIDU,
                    \app\core\payment\Payment::PAY_TYPE_WECHAT_H5,
                    \app\core\payment\Payment::PAY_TYPE_ALIPAY_H5
                ]);
            }
        }
        if (empty($paymentType)) {
            return [
                'code' => 1,
                'msg' => '当前没有可使用的支付方式'
            ];
        }
        $paymentOrders = [];
        foreach ($orders as $order) {
            $paymentOrder = new PaymentOrder([
                'title' => '优惠券兑换订单',
                'amount' => (float)$order->price,
                'orderNo' => $order->order_no,
                'notifyClass' => CouponOrderPayNotify::class,
                'supportPayTypes' => $paymentType,//选填，支持的支付方式，若不填将支持所有支付方式。
            ]);
            $paymentOrders[] = $paymentOrder;
        }
        $id = \Yii::$app->payment->createOrder($paymentOrders);
        return [
            'code' => 0,
            'data' => [
                'id' => $id,
            ],
        ];
    }
}
