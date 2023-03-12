<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\admin;

use app\forms\admin\AdminPluginListForm;
use app\forms\admin\app_manage\AppManageEditForm;
use app\forms\admin\app_manage\AppManageForm;
use app\forms\admin\app_manage\AppOrderForm;
use app\forms\admin\app_manage\PluginForm;
use app\forms\admin\file\FileForm;
use app\forms\admin\order\PreviewOrderForm;


class AppManageController extends AdminController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AdminPluginListForm();
            $form->attributes = \Yii::$app->request->get();
            return $form->search();
        } else {
            return $this->render('index');
        }
    }

    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PluginForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getDetail();

            return $res;
        }

        return $this->render('detail');
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AppManageEditForm();
                $form->attributes = \Yii::$app->request->post();
                $res = $form->edit();
                return $res;
            } else {
                $form = new AppManageForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();

                return $res;
            }
        }

        return $this->render('edit');
    }

    public function actionGroup()
    {
        return $this->render('group');
    }

    public function actionOrder()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AppOrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            $res = $form->getList();
            return $res;
        }
        
        return $this->render('order');
    }

    public function actionPreviewOrder()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new PreviewOrderForm();
                $form->attributes = \Yii::$app->request->post();
                $res = $form->save();
                return $res;
            } else {
                
            }
        }
    }

    public function actionFile()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new FileForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->getList();
            return $res;
        }

        return $this->render('file');
    }

    /**
     * 删除全部
     * @return \yii\web\Response
     */
    public function actionFileDestroyAll()
    {
        $form = new FileForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->destroyAll();

        return $this->asJson($res);
    }

    /**
     * 删除全部
     * @return \yii\web\Response
     */
    public function actionFileDestroy()
    {
        $form = new FileForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->destroy();

        return $this->asJson($res);
    }

    public function actionQueryOrder()
    {
        $form = new AppOrderForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->queryOrder();
        return $res;
    }
}
