<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\fission\forms\receive\validate;

use app\models\User;
use app\plugins\fission\models\FissionActivity;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionActivityReward;
use app\plugins\fission\models\FissionRewardLog;

abstract class BasicModel
{
    public $mall_id;
    public $user;

    public $rewardLogModel;
    public $activityModel;
    public $rewardModel;
    public $activityLogModel;

    public function setMallId($mall_id = '')
    {
        empty($mall_id) && $mall_id = \Yii::$app->mall->id;
        $this->mall_id = $mall_id;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setActivityModel($activity_id)
    {
        if ($activity_id) {
            $this->activityModel = FissionActivity::find()->where([
                'mall_id' => $this->mall_id,
                'id' => $activity_id,
            ])->one();
        } else {
            // 获取当前活动
            $date = date('Y-m-d H:i:s');
            $this->activityModel = FissionActivity::find()->where([
                'AND',
                ['mall_id' => $this->mall_id],
                ['<=', 'start_time', $date],
                ['>', 'end_time', $date],
                ['is_delete' => 0],
                ['status' => 1]
            ])->one();
        }
    }

    public function setActivityLogModel($activity_log_id)
    {
        $this->activityLogModel = FissionActivityLog::find()->where([
            'mall_id' => $this->mall_id,
            'id' => $activity_log_id,
            'user_id' => $this->user->id,
        ])->one();
    }

    public function setRewardModel($id)
    {
        $this->rewardModel = FissionActivityReward::find()->where([
            'mall_id' => $this->mall_id,
            'id' => $id,
            'activity_id' => $this->activityModel->id,
        ])->one();
    }
    public function setRewardLogModel($reward_log_id)
    {
        $this->rewardLogModel = FissionRewardLog::find()->where([
            'mall_id' => $this->mall_id,
            'user_id' => $this->user->id,
            'id' => $reward_log_id,
        ])->one();
    }
}
