<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/18
 * Time: 4:23 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivity;

class ActivityForm extends Model
{
    public $status;
    public $start_time;
    public $end_time;
    public $keyword;

    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['start_time', 'end_time', 'keyword'], 'trim'],
            [['start_time', 'end_time', 'keyword'], 'string'],
            ['status', 'in', 'range' => [-1, 0, 1, 2, 3]]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $query = FissionActivity::find()->with(['logs.rewardLog'])
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($this->keyword !== null, ['like', 'name', $this->keyword]);
            if ($this->start_time && $this->end_time) {
                $query->andWhere(
                    [
                        'or',
                        ['between', 'start_time', $this->start_time, $this->end_time],
                        ['between', 'end_time', $this->start_time, $this->end_time],
                        [
                            'and',
                            [
                                '<=',
                                'start_time',
                                $this->start_time
                            ],
                            [
                                '>=',
                                'end_time',
                                $this->end_time
                            ]
                        ]
                    ]
                );
            }

            if (isset($this->status)) {
                switch ($this->status) {
                    // 全部
                    case -1:
                        break;
                    // 未开始
                    case 0:
                        $query->andWhere(['>', 'start_time', mysql_timestamp()]);
                        $query->andWhere(['status' => 1]);
                        break;
                    // 进行中
                    case 1:
                        $query->andWhere(['<=', 'start_time', mysql_timestamp()]);
                        $query->andWhere(['>=', 'end_time', mysql_timestamp()]);
                        $query->andWhere(['status' => 1]);
                        break;
                    // 已结束
                    case 2:
                        $query->andWhere(['<=', 'end_time', mysql_timestamp()]);
                        $query->andWhere(['status' => 1]);
                        break;
                    // 下架中
                    case 3:
                        $query->andWhere(['status' => 0]);
                        break;
                    default:
                        break;
                }
            }
            $list = $query->orderBy('start_time DESC')->page($pagination)->all();
            $newList = [];
            /** @var FissionActivity[] $list */
            foreach ($list as $activity) {
                $startTime = strtotime($activity->start_time);
                $endTime = strtotime($activity->end_time);
                if ($activity->status == 1 && $startTime > time()) {
                    $status = 0;
                } elseif ($activity->status == 1 && $startTime <= time() && $endTime > time()) {
                    $status = 1;
                } elseif ($activity->status == 1 && $endTime <= time()) {
                    $status = 2;
                } else {
                    $status = 3;
                }
                // 领取红包总人数
                $totalCount = 0;
                // 领取红包总金额
                $totalMoney = 0;
                if ($activity->logs) {
                    foreach ($activity->logs as $log) {
                        if ($log->rewardLog) {
                            foreach ($log->rewardLog as $rewardLog) {
                                if ($rewardLog->reward_type == 0) {
                                    $totalCount++;
                                    $totalMoney += floatval($rewardLog->real_reward);
                                }
                            }
                        }
                    }
                }
                $newList[] = [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'total_count' => $totalCount,
                    'total_money' => price_format($totalMoney),
                    'start_time' => $activity->start_time,
                    'end_time' => $activity->end_time,
                    'status' => $status
                ];
            }
            return $this->success([
                'msg' => '',
                'list' => $newList,
                'pagination' => $pagination
            ]);
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage(),
                'error' => $exception
            ]);
        }
    }
}
