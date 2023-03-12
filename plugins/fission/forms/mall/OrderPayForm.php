<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/29
 * Time: 11:28 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderPayFormBase;
use app\models\Order;
use app\models\OrderSubmitResult;
use app\models\User;

class OrderPayForm extends OrderPayFormBase
{
    public $queue_id;
    public $token;
    public $user_id;

    public function rules()
    {
        return [
            [['queue_id', 'token', 'user_id'], 'required'],
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
        /** @var Order $order */
        $order = Order::find()->where([
            'token' => $this->token,
            'is_delete' => 0,
            'user_id' => $this->getUser()->id,
        ])->one();
        if (!$order) {
            $orderSubmitResult = OrderSubmitResult::findOne([
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
        $res = $this->getReturnData([$order]);
        \Yii::$app->payment->payBuyCash($res['data']['id']);
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'id' => $order->id,
                'order_no' => $order->order_no
            ]
        ];
    }

    public function getUser()
    {
        $user = User::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $this->user_id
        ])->one();

        if (!$user) {
            throw new OrderException('用户不存在');
        }

        return $user;
    }

    public function createOrder($paymentOrders)
    {
        return \Yii::$app->payment->createOrder($paymentOrders, $this->getUser());
    }
}
