<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive;

use app\core\express\exception\Exception;

use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsCards;
use app\models\User;
use app\plugins\fission\forms\common\CommonReward;
use app\plugins\fission\forms\receive\core\ActivityModel;
use app\plugins\fission\forms\receive\core\ConfigModel;
use app\plugins\fission\forms\receive\core\Create;
use app\plugins\fission\forms\receive\core\Reward;
use app\plugins\fission\forms\receive\core\RewardModel;
use app\plugins\fission\forms\receive\exception\ConvertException;
use app\plugins\fission\forms\receive\exception\ExchangeException;
use app\plugins\fission\forms\receive\exception\NoActivityException;
use app\plugins\fission\forms\receive\exception\NoRollBackException;
use app\plugins\fission\forms\receive\validate\FacadeAdmin;
use app\plugins\fission\jobs\ExchangeJob;
use app\plugins\fission\models\FissionActivity;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;


class ExchangeFactory
{
    private $user;
    private $activityModel;

    public $invite_user_id; //参与活动
    public $activity_id;//参与活动
    public $activity_log_id;//兑换
    public $reward_id;//兑换
    public $is_open; //参加活动

    public function __construct($config = [])
    {
        $class = new \ReflectionClass($this);
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (isset($config[$property->getName()])) {
                $property->setValue($this, $config[$property->getName()]);
            }
        }
    }

    public function getActivityModel()
    {
        return $this->activityModel;
    }

    public function activity(&$message)
    {
        $message = '';
        $t = \Yii::$app->db->beginTransaction();
        try {
            $f = new FacadeAdmin();
            $f->create($this->activity_id);
            /** @var FissionActivity $activity */
            $activity = $f->validate->activityModel;
            $this->activityModel = $activity;

            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->user->id,
                'activity_id' => $activity->id,
                'is_delete' => 0,
            ];
            $model = FissionActivityLog::find()->where($where)->one();
            if (empty($model)) {
                if (empty($this->is_open)) throw new NoActivityException('首次不予兑奖', $activity);

                $sql = sprintf(
                    'select * from %s where mall_id = %s and is_delete = %s and activity_id = "%s" and user_id = %s for update',
                    FissionActivityLog::tableName(),
                    $where['mall_id'],
                    $where['is_delete'],
                    $where['activity_id'],
                    $where['user_id']
                );
                $hot = \Yii::$app->db->createCommand($sql)->queryOne();
                if ($hot) throw new \Exception('请刷新重试');

                // 邀请人
                $invite_user_id = 0;
                $invite_activity_log_id = 0;
                if ($this->invite_user_id) {
                    /** @var FissionActivityLog $invite */
                    $invite = FissionActivityLog::find()->where([
                        'mall_id' => \Yii::$app->mall->id,
                        'user_id' => $this->invite_user_id,
                        'activity_id' => $activity->id,
                        'is_delete' => 0,
                    ])->one();
                    if ($invite
                        && !empty($activityData = \yii\helpers\BaseJson::decode($invite->activity))
                        && $activity['end_time'] > date('Y-m-d H:i:s')
                        && $activity['start_time'] <= date('Y-m-d H:i:s')
                    ) {
                        $count = FissionActivityLog::find()->where([
                            'mall_id' => \Yii::$app->mall->id,
                            'invite_user_id' => $invite->user_id,
                            'invite_activity_log_id' => $invite->id,
                            'activity_id' => $activity->id,
                            'is_delete' => 0
                        ])->count();
                        if ($count + 1 < $activityData['number']) {
                            $invite_user_id = $invite->user_id;
                            $invite_activity_log_id = $invite->id;
                        }
                        \Yii::error('邀请人邀请超限');
                    } else {
                        \Yii::error('邀请人无法接受邀请');
                    }
                }

                //CREATE
                $model = new FissionActivityLog();
                $model->attributes = $where;
                $model->invite_user_id = $invite_user_id;
                $model->invite_activity_log_id = $invite_activity_log_id;
                $model->activity = strval(new ActivityModel($activity));
                $model->select_name = $activity->name;
                $model->rewards = strval(new RewardModel($activity));
                if (!$model->save()) {
                    //没继承 model
                    throw new \Exception(current($model->errors)[0]);
                }
                $key = array_search(0, array_column($activity->rewards, 'type'));
                if ($key === false) {
                    throw new \Exception('奖品不存在');
                } else {
                    $activityLogModel = $model;
                    $this->reward_id = $activity->rewards[$key]['id'];
                }

                try {
                    //生成奖品
                    $user = User::findOne($where['user_id']);
                    /** FissionLog $log */
                    $create = new Create();
                    $log = $create->start(
                        $user,
                        $activityLogModel,
                        $this->reward_id
                    );

                    //发放奖品
                    $reward = new Reward();
                    $reward->reward(
                        $user,
                        $log
                    );
                } catch (NoRollBackException $e) {
                    \Yii::error('首次兑换现金和商品');
                }
                $t->commit();
            }
            return $model;
        } catch (ExchangeException $e) {
            //活动参加后状态失效情况
            $t->rollBack();
            $activityModel = $f->validate->activityModel;
            $this->activityModel = $activityModel;
            if ($activityModel) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'user_id' => \Yii::$app->user->id,
                    'activity_id' => $activityModel->id,
                    'is_delete' => 0,
                ];
            } else {
                if ($this->activity_id) {
                    throw new \Exception($e->getMessage());
                } else {
                    $where = [
                        'mall_id' => \Yii::$app->mall->id,
                        'user_id' => \Yii::$app->user->id,
                        'is_delete' => 0,
                    ];
                }
            }
            $model = FissionActivityLog::find()->orderBy("id desc")->where($where)->one();
            if (empty($model)) throw new \Exception($e->getMessage());

            $message = $e->getMessage();
            return $model;
        } catch (ConvertException $e) {
            //兑换失败情况
            $t->rollBack();
            \Yii::error('首次兑换失败：' . $e->getMessage());
            throw new \Exception('活动已结束');
        } catch (NoActivityException $e) {
            //不兑奖情况
            $t->rollBack();
            throw new NoActivityException($e->getMessage(), $activity);
        } catch (Exception $e) {
            $t->rollBack();
            throw new \Exception($e->getMessage());
        }
    }


    public function unite()
    {
        try {
            $user = User::findOne(\Yii::$app->user->id);
            $f = new FacadeAdmin();
            $f->unite($user, $this->activity_log_id, $this->reward_id, $r_reward);

            $queueId = \Yii::$app->queue->delay(0)->push(new ExchangeJob([
                'reward_id' => $this->reward_id,
                'activity_log_id' => $this->activity_log_id,
                'user' => $user,
            ]));
            $status = \Yii::$app->queue->isDone($queueId);
            $t1 = microtime(true);
            while (!$status) {
                sleep(0.25);
                $status = \Yii::$app->queue->isDone($queueId);
                $t2 = microtime(true);
                if (round($t2 - $t1, 3) > 10) {
                    throw new \Exception('队列处理失败');
                }
            }

            /** @var FissionRewardLog $r */
            $r = FissionRewardLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $user->id,
                'reward_type' => $r_reward['type'],
                //'reward_id' => $this->reward_id,
                'activity_log_id' => $this->activity_log_id,
                'is_delete' => 0,
            ])->asArray()->one();
            if (!empty($r)) {
                $r['reward'] = \yii\helpers\BaseJson::decode($r['reward']);
                $r['expire_at'] = $r['expire_time'] != 0 ? (new \DateTime($r['created_at']))->add(new \DateInterval(sprintf('P%sD', $r['expire_time'])))->format('Y-m-d H:i:s') : '';
                $r['reward'] = CommonReward::addInfo($r['reward'], $r['result_id']);
                return $r;
            } else {
                throw new \Exception('兑换失败');
            }
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 手动兑换
     * @param $reward_log_id
     * @param $user_id
     * @return array
     * @throws \Exception
     */
    public function convert($reward_log_id, $user_id)
    {
        try {
            $user = User::findOne($user_id);
            $f = new FacadeAdmin();
            $f->convert($user, $reward_log_id);

            /** @var FissionRewardLog $rewardLog */
            $rewardLog = $f->validate->rewardLogModel;
            $rewardLog->is_exchange = 1;
            $rewardLog->save();
            return [];
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
