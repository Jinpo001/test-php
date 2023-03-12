<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/15
 * Time: 5:08 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\mall;

use app\plugins\fission\controllers\Controller;
use app\plugins\fission\forms\mall\SettingForm;

class SettingController extends Controller
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
        if (\Yii::$app->request->isPost) {
            $form = new SettingForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        } else {
            return $this->render('index');
        }
    }
}
