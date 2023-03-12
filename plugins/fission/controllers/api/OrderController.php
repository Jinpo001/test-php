<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/25
 * Time: 4:13 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\api;

use app\plugins\fission\forms\api\OrderSubmitForm;

class OrderController extends ApiController
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
}
