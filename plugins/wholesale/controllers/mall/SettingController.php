<?php

namespace app\plugins\wholesale\controllers\mall;

use app\plugins\wholesale\forms\mall\WholesaleSettingForm;
use app\plugins\Controller;

class SettingController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new WholesaleSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new WholesaleSettingForm();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('index');
        }
    }
}