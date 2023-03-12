<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/16
 * Time: 10:46 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\mall;

use app\plugins\fission\controllers\Controller;
use app\plugins\fission\forms\mall\LogForm;
use app\plugins\fission\forms\mall\RewardDetailForm;

class LogController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new LogForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new RewardDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('detail');
        }
    }

    public function actionInvite()
    {
        $form = new RewardDetailForm();
        $form->attributes = \Yii::$app->request->get();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getInvite());
    }

    public function actionCash()
    {
        $form = new RewardDetailForm();
        $reward_log_id = \Yii::$app->request->post('reward_log_id');
        $user_id = \Yii::$app->request->post('user_id');
        return $this->asJson($form->cashConvert($reward_log_id, $user_id));
    }

    public function actionDelete()
    {
        $form = new LogForm();
        return $this->asJson($form->delete(\Yii::$app->request->post('activity_log_id')));
    }
}
