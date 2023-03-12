<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\teller\controllers\mall;


use app\plugins\Controller;
use app\plugins\teller\forms\mall\CashierForm;
use app\plugins\teller\forms\mall\CashierModifyForm;
use app\plugins\teller\forms\mall\CashierPushForm;
use app\plugins\teller\forms\mall\CashierStoreForm;

class CashierController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CashierForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    // 保存
    public function actionStore()
    {
        $form = new CashierStoreForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 修改
    public function actionModify()
    {
        $form = new CashierModifyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new CashierForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('edit');
        }
    }

    public function actionDelete()
    {
        $form = new CashierForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    public function actionUpdateStatus()
    {
        $form = new CashierForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->updateStatus());
    }

    public function actionUpdatePassword()
    {
        $form = new CashierForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->updatePassword());
    }

    public function actionPush()
    {
        $form = new CashierPushForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getList());
    }
}
