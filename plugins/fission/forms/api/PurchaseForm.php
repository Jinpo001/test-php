<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/26
 * Time: 3:40 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\api;

use app\plugins\fission\forms\common\CommonActivity;
use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivity;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;

class PurchaseForm extends Model
{
    public function search()
    {
        try {
            /** @var FissionActivity $activity */
            $activity = FissionActivity::find()->with('rewards')
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1
                ])->andWhere([
                    '<=', 'start_time', mysql_timestamp()
                ])->andWhere([
                    '>', 'end_time', mysql_timestamp()
                ])->one();
            if (!$activity) {
                throw new \Exception('活动未开始');
            }
            $logs = FissionActivityLog::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'activity_id' => $activity->id])
                ->select('id');
            $rewardLogs = FissionRewardLog::find()->with('user.userInfo')
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'activity_log_id' => $logs])
                ->orderBy('id DESC')
                ->limit(10)->all();
            $newList = [];
            /** @var FissionRewardLog[] $rewardLogs */
            $rewardList = CommonActivity::getInstance()->getRewards($rewardLogs);
            foreach ($rewardLogs as $item) {
                $nickname = mb_strlen($item->user->nickname, 'UTF-8') > 5
                    ? mb_substr($item->user->nickname, 0, 4, 'UTF-8') . '...'
                    : $item->user->nickname;
                $name = $rewardList[$item->id]['name'];

                $diff = time() - strtotime($item->created_at);
                $minute = floor($diff / 60);
                $hour = floor($minute / 60);

                if ($diff > 24 * 60 * 60) {
                    $time_str = '一天前';
                } elseif ($hour > 0) {
                    $time_str = $hour . '小时前';
                } elseif ($minute > 0) {
                    $time_str = $minute . '分钟前';
                } else {
                    $time_str = $diff . '秒前';
                }
                array_push($newList, [
                    'content' => sprintf('%s领取了%s', $nickname, $name),
                    'time_str' => $time_str,
                    'avatar' => $item->user->userInfo->avatar,
                ]);
            }

            return $this->success($newList);
        } catch (\Exception $e) {
            return $this->fail([
                'msg' => $e->getMessage()
            ]);
        }
    }
}
