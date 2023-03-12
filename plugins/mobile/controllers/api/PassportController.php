<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/10
 * Time: 9:20 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\controllers\api;

use app\plugins\mobile\forms\api\RegisterForm;

class PassportController extends ApiController
{
    public function actions()
    {
        return [
            'register-data' => [
                'class' => '\app\plugins\mobile\components\RegisterSettingAction'
            ],
        ];
    }

    public function actionRegister()
    {
        $form = new RegisterForm();
        $form->scenario = 'register';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->register());
    }

    public function actionSmsCaptcha()
    {
        $form = new RegisterForm();
        $form->scenario = 'send_sms_captcha';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->sendSmsCaptcha());
    }

    public function actionLogout()
    {
        \Yii::$app->user->logout();
        return $this->asJson([
            'code' => 0,
            'msg' => '退出登录成功'
        ]);
    }
}
