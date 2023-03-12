<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\wholesale\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\wholesale\forms\api\CartForm;

class CartController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionAddCart()
    {
        $form = new CartForm();
        $form->attributes = \Yii::$app->request->post();

        return $this->asJson($form->addCart());
    }
}
