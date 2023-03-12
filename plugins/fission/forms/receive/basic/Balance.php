<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\basic;

class Balance extends BaseAbstract implements Base
{
    public function exchange(&$message,&$log)
    {
        try {
            $balance = floatval($this->rewardLog->real_reward);
            $desc = sprintf('红包墙%s奖励兑换%s余额', '', $balance);
            return \Yii::$app->currency->setUser($this->user)->balance->add($balance, $desc) === true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}
