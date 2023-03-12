<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\controllers\admin;


use app\core\response\ApiCode;
use app\forms\admin\MessageRemindSettingForm;
use app\forms\admin\SmsCaptchaForm;
use app\forms\admin\license\LicenseEditForm;
use app\forms\admin\license\LicenseForm;
use app\forms\admin\user\BatchPermissionForm;
use app\forms\admin\user\RegisterAuditForm;
use app\forms\admin\user\UserBindForm;
use app\forms\admin\user\UserEditForm;
use app\forms\admin\user\UserForm;
use app\forms\common\CommonAuth;
use app\forms\common\CommonUser;
use app\forms\common\attachment\CommonAttachment;
use app\models\User;

class LicenseController extends AdminController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LicenseForm();
            $form->attributes = \Yii::$app->request->get();

            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionAdd()
    {
        $form = new LicenseEditForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->add());
    }

    public function actionUpdate()
    {
        $form = new LicenseEditForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->update());
    }

    public function actionDestroy()
    {
        $form = new LicenseForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->destroy());
    }
}
