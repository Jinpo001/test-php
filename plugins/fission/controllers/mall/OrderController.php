<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/29
 * Time: 1:51 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\mall;

use app\plugins\fission\controllers\Controller;
use app\plugins\fission\forms\mall\OrderPayForm;
use app\plugins\fission\forms\mall\OrderSubmitForm;

class OrderController extends Controller
{
    public function actionPreview()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));
        return $this->asJson($form->setPluginData()->preview());
    }

    public function actionSubmit()
    {
        $form = new OrderSubmitForm();
        $form->form_data = \Yii::$app->serializer->decode(\Yii::$app->request->post('form_data'));

        return $this->asJson($form->setPluginData()->submit());
    }

    public function actionPayData()
    {
        $form = new OrderPayForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getResponseData());
    }
}
