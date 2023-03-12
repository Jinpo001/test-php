<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/17
 * Time: 9:54 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\plugins\mobile\forms\api\UserForm;

class UserController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    // 修改头像
    public function actionAvatar()
    {
        $form = new UserForm();
        $form->scenario = 'u_avatar';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->avatar());
    }

    // 修改昵称
    public function actionNickname()
    {
        $form = new UserForm();
        $form->scenario = 'u_nickname';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->nickname());
    }

    // 修改密码
    public function actionPassword()
    {
        $form = new UserForm();
        $form->scenario = \Yii::$app->request->post('type');
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->password());
    }

    // 修改手机号
    public function actionMobile()
    {
        $form = new UserForm();
        $form->scenario = 'u_mobile';
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->mobile());
    }

    // 验证身份
    public function actionValidateIdentity()
    {
        $form = new UserForm();
        $form->scenario = \Yii::$app->request->post('type');
        $form->attributes = \Yii::$app->request->post();
        $form->mobile = \Yii::$app->user->identity->mobile;
        return $this->asJson($form->validateIdentity());
    }

    public function actionContact()
    {
        $form = new UserForm();
        return $this->asJson($form->contact());
    }
}
