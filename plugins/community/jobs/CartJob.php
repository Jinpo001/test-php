<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/4/10
 * Time: 13:56
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\jobs;


use app\jobs\BaseJob;
use app\models\Mall;
use app\models\OrderSubmitResult;
use app\models\User;
use app\plugins\community\forms\api\cart\CartForm;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Class CartJob
 * @package app\plugins\community\jobs
 * @property CartForm $form
 * @property Mall $mall
 * @property User $user
 */
class CartJob extends BaseJob implements JobInterface
{
    public $form;
    public $token;
    public $mall;
    public $user;
    public $appVersion;
    public $appPlatform;

    public function execute($queue)
    {
        $this->setRequest();
        \Yii::$app->setMall($this->mall);
        \Yii::$app->user->setIdentity($this->user);
        \Yii::$app->setAppVersion($this->appVersion);
        \Yii::$app->setAppPlatform($this->appPlatform);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->form->save();
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::error($exception->getMessage());
            \Yii::error($exception);
            $orderSubmitResult = new OrderSubmitResult();
            $orderSubmitResult->token = $this->token;
            $orderSubmitResult->data = $exception->getMessage();
            $orderSubmitResult->save();
            throw $exception;
        }
    }
}
