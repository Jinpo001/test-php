<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\basic;

class Integral extends BaseAbstract implements Base
{
    public function exchange(&$message,&$log)
    {
        try {
            $integral_num = intval($this->reward['min_number']);
            $desc = sprintf('红包墙%s奖励兑换%s积分', '', $integral_num);
            return \Yii::$app->currency->setUser($this->user)->integral->add($integral_num, $desc) === true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}