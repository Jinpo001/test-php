<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\wholesale\controllers\api;


use app\controllers\api\filters\LoginFilter;
use app\plugins\wholesale\forms\api\OrderSubmitForm;

class OrderController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    public function actionOrderPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionOrderSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->submit());
    }

}
