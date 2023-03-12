<?php declare(strict_types=1);


namespace app\controllers\mall;


use app\forms\mall\goods\GoodsAttrTemplateForm;
use app\forms\mall\goods\params\GoodsParamsTemplateForm;

class GoodsParamsTemplateController extends MallController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsParamsTemplateForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->get());
        } else {
            return $this->render('index');
        }
    }

    public function actionPost()
    {
        if (\Yii::$app->request->isPost) {
            $form = new GoodsParamsTemplateForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionDestroy()
    {
        if (\Yii::$app->request->isPost) {
            $form = new GoodsParamsTemplateForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->destroy());
        }
    }
}