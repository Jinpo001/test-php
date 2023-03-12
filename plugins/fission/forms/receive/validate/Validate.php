<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\validate;

use Algorithm\sort;
use app\models\User;
use app\plugins\fission\forms\receive\exception\ExchangeException;
use app\plugins\fission\forms\receive\exception\NoActivityException;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;
use yii\db\Exception;

class Validate extends BasicModel
{
    public function hasUser()
    {
        if (!($this->user instanceof User)) {
            throw new Exception('用户不存在');
        }
    }

    public function hasActivityLogModel()
    {
        if (!$this->activityLogModel || $this->activityLogModel->is_delete != 0) {
            throw new ExchangeException('用户墙不存在');
        }
    }

    public function hasRewardLogModel()
    {
        if (!$this->rewardLogModel || $this->rewardLogModel->is_delete != 0) {
            throw new ExchangeException('活动不存在或已删除');
        }
    }

    public function hasActivityModel()
    {
        if (!$this->activityModel || $this->activityModel->is_delete != 0) {
            throw new ExchangeException('活动不存在或已删除');
        }
    }

    public function hasActivityInfo()
    {
        if ($this->activityModel->status == 0) {
            throw new ExchangeException('活动已下架');
        }
        $date = date('Y-m-d H:i:s');
        if ($this->activityModel->start_time >= $date) {
            throw new ExchangeException('活动未开始');
        }

        if ($this->activityModel->end_time <= $date) {
            throw new ExchangeException('该活动已到期，无法参与');
        }
    }


    public function testActivityStatus()
    {
        $activity = \yii\helpers\BaseJson::decode($this->activityLogModel->activity);
        if ($activity['end_time'] < date('Y-m-d H:i:s')) {
            throw new ExchangeException('活动已结束');
        }
    }

    public function testRewardStatus($reward_id, &$r_reward = null)
    {
        $rewards = \yii\helpers\BaseJson::decode($this->activityLogModel->rewards);
        usort($rewards, function ($item, $item2) {
            return $item['type'] <=> $item2['type'];
        });

        $people_number = 0;
        foreach ($rewards as $reward) {
            if ($reward['type'] && $reward['level'] === 'main') {
                $people_number += $reward['people_number'];
            }
            if ($reward['id'] == $reward_id) {
                $r_reward = $reward;
                break;
            }
        }
        if (is_null($r_reward)) {
            throw new ExchangeException('关卡不存在');
        }
        //////////////////////邀请人数
        $p = FissionActivityLog::find()->where([
            'mall_id' => $this->mall_id,
            'is_delete' => 0,
            'invite_user_id' => $this->user->id,
            'activity_id' => $this->activityLogModel->activity_id,
            'invite_activity_log_id' => $this->activityLogModel->id,
        ])->count();
        if ($p < $people_number) {
            throw new ExchangeException('邀请人数不足');
        }
        ///////////////////////重复领取判断
        $exists = FissionRewardLog::find()->where([
            'mall_id' => $this->mall_id,
            'user_id' => $this->user->id,
            'reward_type' => $r_reward['type'],
            'activity_log_id' => $this->activityLogModel->id,
            'is_delete' => 0,
        ])->exists();

        if ($exists) {
            throw new ExchangeException('无法重复领取');
        }
    }

    public function testExchangeExpired()
    {
        $backup_data = \yii\helpers\BaseJson::decode($this->activityLogModel->activity);
        $day = $backup_data['expire_time'];
        if ($day) {
            $start_at = $this->rewardLogModel->created_at;
            $start = (new \DateTime($start_at))->add(new \DateInterval(sprintf('P%sD', $day)))->format('Y-m-d H:i:s');
            if (date('Y-m-d H:i:s') > $start) {
                throw new ExchangeException('奖励已过期，无法领取');
            }
        }
    }

    public function hasRewardConvert()
    {
        $is_exchange = $this->rewardLogModel->is_exchange;
        if ($is_exchange) {
            throw new ExchangeException('奖励已兑换');
        }
    }
}
