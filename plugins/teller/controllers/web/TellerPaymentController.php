<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 16:01
 */


namespace app\plugins\teller\controllers\web;


use app\plugins\teller\controllers\web\TellerController;
use app\plugins\teller\forms\OrderQueryForm;
use app\plugins\teller\forms\web\TellerOrderPayForm;
use app\plugins\teller\forms\web\TellerOrderSubmitForm;
use app\plugins\teller\forms\web\order\TellerPayment;

class TellerPaymentController extends TellerController
{
    /**
     * @param integer $id PaymentOrderUnion id
     * @param string $pay_type
     * @return array
     * @throws \app\core\payment\PaymentException
     */
    public function actionPayData($id, $pay_type)
    {
        $payment = new TellerPayment();
        $payment->user_id = \Yii::$app->request->get('user_id');
        $payment->auth_code = \Yii::$app->request->get('auth_code');
        $res = $payment->onLinePay($id, $pay_type);

        return $this->asJson($res);
    }

    public function actionOtherPay()
    {
        $payment = new TellerPayment();
        $payment->user_id = \Yii::$app->request->post('user_id');
        $payment->balance_type = \Yii::$app->request->post('balance_type');
        $payment->pay_password = \Yii::$app->request->post('pay_password');
        $payment->pay_code = \Yii::$app->request->post('pay_code');
        $id = \Yii::$app->request->post('id');
        $payType = \Yii::$app->request->post('pay_type');
        $res = $payment->otherPay($id, $payType);

        return $this->asJson($res);
    }

    // 撤销订单
    // public function actionPayReverse()
    // {
    //     $form = new OrderQueryForm();
    //     $form->id = \Yii::$app->request->get('id');
    //     $res = $form->reverse();

    //     return $this->asJson($res);
    // }
}
