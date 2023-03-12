<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\validate;

class FacadeAdmin
{
    public $validate;

    public function __construct()
    {
        $this->validate = new Validate();
    }

    public function create($activity_id)
    {
        $this->validate->setMallId();
        $this->validate->setActivityModel($activity_id);
        $this->validate->hasActivityModel();
        $this->validate->hasActivityInfo();
    }

    public function unite($user, $activity_log_id, $reward_id,&$r_reward = null)
    {
        $this->validate->setUser($user);
        $this->validate->hasUser();
        $this->validate->setMallId($this->validate->user->mall_id);

        $this->validate->setActivityLogModel($activity_log_id);
        $this->validate->hasActivityLogModel();

        $this->validate->setActivityModel($this->validate->activityLogModel->activity_id);
        $this->validate->hasActivityModel();
        $this->validate->hasActivityInfo();

        //  $this->validate->testActivityStatus();//活动状态判断
        $this->validate->testRewardStatus($reward_id,$r_reward);//奖励判断
    }


    public function convert($user, $reward_log_id)
    {
        $this->validate->setUser($user);
        $this->validate->hasUser();
        //防止操作前台商城 ???
        $this->validate->setMallId($this->validate->user->mall_id);
        $this->validate->setRewardLogModel($reward_log_id);
        $this->validate->hasRewardLogModel();
        $this->validate->setActivityLogModel($this->validate->rewardLogModel->activity_log_id);
        $this->validate->hasActivityLogModel();
        //去除活动结束判断
        //$this->validate->testActivityStatus();
        $this->validate->hasRewardConvert();
        $this->validate->testExchangeExpired();
    }
}
