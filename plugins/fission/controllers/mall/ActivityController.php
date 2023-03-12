<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/16
 * Time: 10:46 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\mall;

use app\plugins\fission\controllers\Controller;
use app\plugins\fission\forms\mall\ActivityDetailForm;
use app\plugins\fission\forms\mall\ActivityEditForm;
use app\plugins\fission\forms\mall\ActivityForm;
use app\plugins\fission\forms\mall\ActivityOperateForm;

class ActivityController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new ActivityForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new ActivityEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new ActivityDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionOperate()
    {
        $form = new ActivityOperateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->operate());
    }
}
