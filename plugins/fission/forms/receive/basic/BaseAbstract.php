<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\basic;

use app\models\User;
use app\plugins\fission\models\FissionRewardLog;

class BaseAbstract
{
    protected $user;
    protected $reward;
    protected $rewardLog;
    public function __construct(User $user, array $reward, FissionRewardLog $log)
    {
        $this->user = $user;
        $this->reward = $reward;
        $this->rewardLog = $log;
    }
}