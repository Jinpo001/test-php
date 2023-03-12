<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 10:46
 */


namespace app\plugins\teller\forms\web\order;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderPayForm;
use app\models\Order;
use app\models\OrderSubmitResult;
use app\models\User;

class TellerOrderPayForm extends OrderPayForm
{
    public $user_id;

    public function rules()
    {
    	return array_merge(parent::rules(), [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ]);
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
        /** @var Order[] $orders */
        $orders = Order::find()->where([
            'token' => $this->token,
            // 'is_delete' => 0, // 此处不加判断是 收银台订单创建时is_delete为0 
            'user_id' => $this->getUser()->id,
        ])->all();
        if (!$orders || !count($orders)) {
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
        return $this->getReturnData($orders);
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
