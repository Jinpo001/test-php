<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\teller\controllers\mall;


use app\plugins\Controller;
use app\plugins\teller\forms\mall\MallWorkLogPrint;
use app\plugins\teller\forms\mall\ShiftsForm;
use app\plugins\teller\forms\mall\TellerSettingEditForm;
use app\plugins\teller\forms\mall\TellerSettingForm;

class ShiftsController extends Controller
{   
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ShiftsForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionShow()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ShiftsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->show());
        } else {
            return $this->render('show');
        }
    }

    public function actionGoods()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ShiftsForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getGoods());
        }
    }

    public function actionOrders()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ShiftsForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getOrders());
        }
    }

    public function actionRefundOrders()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ShiftsForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getRefundOrders());
        }
    }

    public function actionPrint()
    {
        $form = new MallWorkLogPrint();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->print());
    }
}
