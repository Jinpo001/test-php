<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/2
 * Time: 10:40
 */

namespace app\plugins\community\controllers\api;



use app\plugins\community\forms\api\poster_share\PosterNewForm;

class PosterShareController extends ApiController
{
    public function actionConfig()
    {
        dd(1);
//        $form = new PosterConfigForm();
//        $form->attributes = \Yii::$app->request->get();
//        return $this->asJson($form->getDetail());
    }

    public function actionGenerate()
    {
        $form = new PosterNewForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->poster());
    }
}
