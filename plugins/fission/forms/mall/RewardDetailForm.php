<?php

namespace app\plugins\fission\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\platform\PlatformConfig;
use app\forms\mall\export\CommonExport;
use app\models\Order;
use app\models\User;
use app\plugins\fission\forms\common\CommonReward;
use app\plugins\fission\forms\Model;
use app\plugins\fission\forms\receive\ExchangeFactory;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;
use Helper\Api;

class RewardDetailForm extends Model
{
    public $activity_log_id;
    public $start_time;
    public $end_time;
    public $keyword;
    public $flag;
    public $choose_list;
    public $fields = [];

    public function rules()
    {
        return [
            [['start_time', 'end_time'], 'trim'],
            ['activity_log_id', 'integer'],
            [['keyword', 'flag'], 'string'],
            [['choose_list', 'fields'], 'safe']
        ];
    }

    public function cashConvert($reward_log_id, $user_id)
    {
        try {
            $form = new ExchangeFactory();
            $form->convert($reward_log_id, $user_id);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getInvite()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $me = FissionActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->activity_log_id,
                'is_delete' => 0
            ])->one();
            if (empty($me)) {
                throw new \Exception('数据为空');
            };
            $query = FissionActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'invite_user_id' => $me->user_id,
                'invite_activity_log_id' => $me->id,
                'activity_id' => $me->activity_id,
                'is_delete' => 0
            ])->keyword($this->start_time && $this->end_time, [
                'AND',
                ['>', 'created_at', $this->start_time],
                ['<', 'created_at', $this->end_time]
            ]);

            if ($this->keyword) {
                $subQuery = User::find()->alias('u')->select('id')->where(['like', 'nickname', $this->keyword])
                    ->andWhere(['u.mall_id' => \Yii::$app->mall->id]);
                $query->andWhere([
                    'OR',
                    ['user_id' => $subQuery],
                    ['like', 'user_id', $this->keyword]
                ]);
            }

            if ($this->flag == "EXPORT") {
                if ($this->choose_list && count($this->choose_list) > 0) {
                    $query->andWhere(['id' => $this->choose_list]);
                }

                $queueId = CommonExport::handle([
                    'export_class' => 'app\\plugins\\fission\\forms\\mall\\RewardExport',
                    'params' => [
                        'query' => $query,
                        'fieldsKeyList' => $this->fields,
                    ]
                ]);
                return $this->success([
                    'queue_id' => $queueId
                ]);
            }
            $logs = $query->page($pagination)->all();
            $logs = array_map(function ($log) {
                $re = \yii\helpers\BaseJson::decode($log->first->reward);
                $user = $log->first->user;
                return [
                    'status' => $re['status'],
                    'id' => $log->id,
                    'avatar' => $user->userInfo->avatar,
                    'nickname' => $user->nickname,
                    'platform_text' => PlatformConfig::getInstance()->getPlatformText($user),
                    'platform_icon' => PlatformConfig::getInstance()->getPlatformIcon($user),
                    'real_reward' => $log->first->real_reward,
                    'created_at' => $log->first->created_at
                ];
            }, $logs);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'list' => $logs,
                    'pagination' => $pagination,
                    'export_list' => (new RewardExport())->fieldsList(),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activityLog = FissionActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->activity_log_id,
                'is_delete' => 0
            ])->with('rewardLog')->one();
            if (empty($activityLog) || empty($rewardLogModel = $activityLog->rewardLog)) {
                throw new \Exception('记录不存在');
            }
            $activityData = \yii\helpers\BaseJson::decode($activityLog->activity);

            $rewards = (new CommonReward)->homeFormat($activityLog);
            $rewards = array_values(array_filter($rewards, function ($reward) {
                return in_array($reward['is_exchange'], [0, 1, 2]);
            }));

            foreach ($rewards as $key => $reward) {
                $rewards[$key]['order_id'] = '';
                $rewards[$key]['order_no'] = '';
                $rewards[$key]['is_send'] = 0;

                if ($reward['status'] === 'goods'
                    && $reward['is_exchange'] == 1
                    && $order = Order::findOne($reward['result_id'])
                ) {
                    $rewards[$key]['is_send'] = $order->is_send;
                    $rewards[$key]['order_no'] = $order->order_no;
                    $rewards[$key]['order_id'] = $order->id;
                }
            }
            $user = User::findOne($activityLog['user_id']);
            $share_number = FissionActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'invite_user_id' => $user->id,
                'invite_activity_log_id' => $activityLog->id,
                'activity_id' => $activityData['id'],
                'is_delete' => 0
            ])->count();
            $share_number++;

            $activity = [
                'user_id'=> $user->id,
                'rewards' => array_values($rewards),
                'avatar' => $user->userInfo->avatar,
                'nickname' => $user->nickname,
                'platform_text' => PlatformConfig::getInstance()->getPlatformText($user),
                'platform_icon' => PlatformConfig::getInstance()->getPlatformIcon($user),
                'name' => $activityData['name'],
                'share_number' => $share_number,
                'last_share_number' => bcsub($activityData['number'], $share_number, 0),
                'created_at' => $activityLog['created_at'],
            ];
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' =>  '获取成功',
                'data' => $activity
            ];
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage(),
                'error' => $exception
            ]);
        }
    }
}
