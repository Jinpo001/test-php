<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/21
 * Time: 3:31 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\wechat\forms\api\WechatForm;

class PassportController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'only' => ['update']
            ],
        ]);
    }

    public function actionCheck()
    {
        $form = new WechatForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->result());
    }

    public function actionLoginUrl()
    {
        $form = new WechatForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->loginUrl();
    }

    public function actionUpdate()
    {
        $form = new WechatForm();
        return $this->asJson($form->updateSubscribe());
    }
}
