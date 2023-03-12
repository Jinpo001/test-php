<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\plugins\teller\controllers\mall;


use app\plugins\Controller;
use app\plugins\teller\forms\mall\SalesForm;
use app\plugins\teller\forms\mall\SalesModifyForm;
use app\plugins\teller\forms\mall\SalesStoreForm;

class SalesController extends Controller
{   
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SalesForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    // 保存
    public function actionStore()
    {
        $form = new SalesStoreForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 修改
    public function actionModify()
    {
        $form = new SalesModifyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    // 详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SalesForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('detail');
        }
    }

    public function actionDelete()
    {
        $form = new SalesForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    public function actionUpdateStatus()
    {
        $form = new SalesForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->updateStatus());
    }
}
