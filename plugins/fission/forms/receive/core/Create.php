<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\core;

use app\models\Model;
use app\models\User;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;
use yii\helpers\BaseJson;

class Create extends Model
{
    public static function getRealReward($reward)
    {
        if ($reward['send_type'] === 'random') {
            if ($reward['min_number'] > $reward['max_number'] || $reward['min_number'] < 0) {
                throw new \Exception('数据不合法');
            }
            $min = $reward['min_number'] * 100;
            $max = $reward['max_number'] * 100;
            $r = mt_rand() % ($max - $min + 1) + $min;
            return $r / 100;
        } else {
            return $reward['min_number'];
        }
    }

    public function start(User $user, FissionActivityLog $activityLogModel, $reward_id, &$secondary = null)
    {
        $rewards = BaseJson::decode($activityLogModel->rewards);
        $activity = BaseJson::decode($activityLogModel->activity);

        $key = array_search($reward_id, array_column($rewards, 'id'));
        if ($key === false) {
            throw new \Exception('奖励找不到');
        }
        $reward = $rewards[$key];

        //获取次要奖品信息
        if ($reward['level'] === 'main') {
            foreach ($rewards as $r) {
                if ($r['level'] === 'secondary' && $r['type'] === $reward['type']) {
                    $secondary = $r;
                    break;
                }
            }
        }
        $log = new FissionRewardLog();
        $log->mall_id = $user->mall_id;
        $log->user_id = $user->id;
        $log->expire_time = $activity['expire_time'];
        $log->reward_id = $reward_id;
        $log->reward_type = $reward['type'];
        $log->activity_log_id = $activityLogModel->id;
        $log->reward = BaseJson::encode($reward);
        $log->real_reward = self::getRealReward($reward);
        $log->is_exchange = 0;
        $log->is_delete = 0;
        $log->save();
        return $log;
    }
}
