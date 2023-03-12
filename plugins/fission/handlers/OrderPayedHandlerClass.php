<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/29
 * Time: 11:44 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\handlers;


use app\plugins\fission\models\FissionRewardLog;

class OrderPayedHandlerClass extends \app\handlers\orderHandler\OrderPayedHandlerClass
{
    protected function pay()
    {
        \Yii::error('--mall pay--');
        parent::pay()->updateRewards();
        return $this;
    }

    protected function updateRewards()
    {
        $rewardLog = FissionRewardLog::findOne([
            'mall_id' => $this->event->order->mall_id, 'result_id' => $this->event->order->id
        ]);
        $rewardLog->is_exchange = 1;
        $rewardLog->save();
    }
}
