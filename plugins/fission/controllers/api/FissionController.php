<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\controllers\api;


use app\controllers\api\ApiController;
use app\controllers\behaviors\LoginFilter;
use app\plugins\fission\forms\api\PosterForm;
use app\plugins\fission\forms\api\ReceiveForm;

class FissionController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionActivity()
    {
        $form = new ReceiveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->activity());
    }

    public function actionUnite()
    {
        $form = new ReceiveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->unite());
    }

    public function actionPoster()
    {
        $form = new PosterForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}