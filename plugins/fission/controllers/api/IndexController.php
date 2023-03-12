<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/21
 * Time: 2:38 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\api;

use app\plugins\fission\forms\api\ActivityForm;
use app\plugins\fission\forms\api\ActivityLogForm;
use app\plugins\fission\forms\api\GoodsForm;
use app\plugins\fission\forms\api\PurchaseForm;

class IndexController extends ApiController
{
    public function actions()
    {
        return [
            'setting-data' => [
                'class' => '\app\plugins\fission\components\SettingDataAction'
            ],
        ];
    }

    public function actionIndex()
    {
        $form = new ActivityForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionLog()
    {
        $form = new ActivityLogForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getReward());
    }

    public function actionPurchase()
    {
        $form = new PurchaseForm();
        return $this->asJson($form->search());
    }

    public function actionGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getDetail());
    }

    public function actionWechat()
    {
        return $this->asJson([
            'code' => 0,
            'msg' => ''
        ]);
    }
}