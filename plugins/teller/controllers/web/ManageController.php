<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\teller\controllers\web;


use app\plugins\Controller;
use app\plugins\teller\forms\mall\ShiftsForm;
use app\plugins\teller\forms\web\ManageIndexForm;
use app\plugins\teller\forms\web\UpdatePasswordForm;
use app\plugins\teller\forms\web\WebWorkLogPrint;

class ManageController extends TellerController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ManageIndexForm();
            return $this->asJson($form->search());
        } else {
            return $this->render('index');
        }
    }

    public function actionUpdatePassword()
    {
    	$form = new UpdatePasswordForm();
    	$form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->save());
    }

    public function actionOffDuty()
    {
        $form = new ManageIndexForm();
        return $this->asJson($form->offDuty());
    }

    public function actionWorkLog()
    {
        $form = new ManageIndexForm();
        return $this->asJson($form->getWorkLog());
    }

    public function actionPrint()
    {
        $form = new WebWorkLogPrint();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->print());
    }
}
