<?php


namespace app\plugins\fission\forms\api;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\fission\forms\common\CommonReward;
use app\plugins\fission\forms\receive\exception\NoActivityException;
use app\plugins\fission\forms\receive\ExchangeFactory;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;

class ReceiveForm extends Model
{
    public $invite_user_id;
    public $activity_id;
    public $activity_log_id;
    public $reward_id;
    public $is_open;

    public function rules()
    {
        return [
            [['invite_user_id', 'activity_id', 'activity_log_id', 'reward_id'], 'integer'],
            [['invite_user_id', 'is_open'], 'default', 'value' => 0],
        ];
    }


    public function unite()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $create = new ExchangeFactory($this->attributes);
            $result = $create->unite();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '兑换成功',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }


    public function activity()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $create = new ExchangeFactory($this->attributes);
            /** @var FissionActivityLog $result */
            $model = $create->activity($message);
            $activityModel = \yii\helpers\ArrayHelper::toArray($create->getActivityModel());
            $rewards = (new CommonReward)->homeFormat($model);

            $first = array_shift($rewards);
            if ($first['type'] != 0) throw new \Exception('首次红包不存在');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $message,
                'data' => [
                    'activity_log_id' => $model->id,
                    'first_rewards' => $first,
                    'rewards' => $rewards,
                    'activity' => array_merge($activityModel, array_filter(\yii\helpers\BaseJson::decode($model->activity), function ($item, $key) {
                        //new-old数据使用
                        return in_array($key, ['number']);
                    }, ARRAY_FILTER_USE_BOTH)),
                    'invite_user' => (function ($model) {
                        if (!$model->invite) {
                            return null;
                        }
                        return [
                            'nickname' => $model->invite->nickname,
                            'avatar' => $model->invite->userInfo->avatar,
                        ];
                    })($model),
                    'child_user' => (function ($model) {
                        $child = FissionActivityLog::find()->select('id')->where([
                            'mall_id' => \Yii::$app->mall->id,
                            'invite_user_id' => \Yii::$app->user->id,
                            'invite_activity_log_id' => $model->id,
                            'activity_id' => $model->activity_id,
                            'is_delete' => 0
                        ])->column();
                        $logs = FissionRewardLog::find()->where([
                            'mall_id' => \Yii::$app->mall->id,
                            'activity_log_id' => $child,
                            'reward_type' => 0,
                            'is_delete' => 0
                        ])->with('user')->all();
                        return array_map(function ($item) {
                            $r = \yii\helpers\BaseJson::decode($item->reward);
                            return CommonReward::addInfo([
                                'send_type' => $r['send_type'],
                                'model_id' => $r['model_id'],
                                'attr_id' => $r['attr_id'],
                                'nickname' => $item->user->nickname,
                                'avatar' => $item->user->userInfo->avatar,
                                'status' => $r['status'],
                                'real_reward' => $item->real_reward,
                                'min_number' => $r['min_number'],
                                'max_number' => $r['max_number'],
                            ]);
                        }, $logs);
                    })($model),
                ]
            ];
        } catch (NoActivityException $e) {
            $activity = $e->getActivity();
            $s = new \stdClass();
            $s->rewards = \yii\helpers\BaseJson::encode($activity->rewards);
            $s->activity_id = -1;
            $s->id = -1;
            $s->user_id = -1;
            $rewards = (new CommonReward)->homeFormat($s);
            $first = array_shift($rewards);

            if ($first['status'] === 'coupon' && !(new CommonReward)->couponValidate($first)) {
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => '活动已结束',
                    'data' => [
                        'text' => '优惠券不足'
                    ]
                ];
            }

            if ($first['type'] != 0) throw new \Exception('首次红包不存在');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'activity_log_id' => 0,
                    'first_rewards' => $first,
                    'rewards' => $rewards,
                    'activity' => $activity,
                    'invite_user' => (function ($invite_user_id) {
                        if (!$invite_user_id || !$user = User::findOne($invite_user_id)) {
                            return null;
                        }
                        return [
                            'nickname' => $user->nickname,
                            'avatar' => $user->userInfo->avatar,
                        ];
                    })($this->invite_user_id),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
