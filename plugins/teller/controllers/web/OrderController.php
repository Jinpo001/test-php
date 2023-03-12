<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/14 16:01
 */


namespace app\plugins\teller\controllers\web;


use app\core\response\ApiCode;
use app\plugins\teller\controllers\web\TellerController;
use app\plugins\teller\forms\web\TellerOrderForm;
use app\plugins\teller\forms\web\TellerRefundForm;
use app\plugins\teller\forms\web\TellerRefundSubmitForm;
use app\plugins\teller\forms\web\order\TellerOrderPayForm;
use app\plugins\teller\forms\web\order\TellerOrderSubmitForm;
use app\plugins\teller\forms\web\order\TellerRechargeOrderForm;

class OrderController extends TellerController
{
    public function actionPreview()
    {
        $form = new TellerOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionSubmit()
    {
        $form = new TellerOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));

        // 由于this->extraOrder()方法函数中 \Yii::$app->user->id 和 当前登录用户\Yii::$app->user->Id不同
        // 所以先存储
        $form->form_data['cashier_id'] = \Yii::$app->user->id;

        return $this->asJson($form->setPluginData()->setSubmitData()->submit());
    }

    public function actionPayData()
    {
        $form = new TellerOrderPayForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getResponseData());
    }

    public function actionCoupon()
    {
        $form = new TellerOrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->getCoupon());
    }

    // 余额充值
    public function actionRechargeOrder()
    {
        $form = new TellerRechargeOrderForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->balanceRecharge());
    }
    // 订单列表
    public function actionOrderList()
    {
        $form = new TellerOrderForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    // 订单详情
    public function actionOrderShow()
    {
        $form = new TellerOrderForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->orderShow());
    }

    // 商家添加订单备注
    public function actionSellerRemark()
    {
        $form = new TellerOrderForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->sellerRemark());
    }

    /**
     * 售后 生成退换货订单
     * @return \yii\web\Response
     */
    public function actionRefundSubmit()
    {
        $form = new TellerRefundSubmitForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->submit());
    }

    public function actionRefundDetail()
    {
        $form = new TellerRefundForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->refundDetail());
    }

    public function actionOrderCancel()
    {
        $form = new TellerOrderForm();
        $form->attributes = \Yii::$app->request->get();

        return $this->asJson($form->orderCancel());
    }
}
