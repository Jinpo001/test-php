<?php


namespace app\plugins\fission\forms\mall;


use app\forms\common\platform\PlatformConfig;
use app\forms\mall\export\CommonExport;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsCards;
use app\models\User;
use app\plugins\fission\forms\common\CommonReward;
use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;
use app\plugins\mch\models\Mch;

class LogForm extends Model
{
    public $start_time;
    public $end_time;
//    public $keyword;
    public $activity_id;
    public $keyword_name;
    public $keyword_value;
    public $flag;
    public $choose_list = [];
    public $fields = [];
    public $page;

    public function rules()
    {
        return [
            [['activity_id', 'page'], 'integer'],
            [['start_time', 'end_time'], 'trim'],
            [['start_time', 'end_time', 'keyword_name', 'keyword_value', 'flag'], 'string'],
            [['choose_list', 'fields'], 'safe'],
            ['page', 'default', 'value' => 1],
        ];
    }

    public function delete($id)
    {
        try {
            /** @var FissionActivityLog $model */
            $model = FissionActivityLog::find()->where([
                'id' => $id,
                'is_delete' => 0
            ])->one();
            if (empty($model)) {
                throw new \Exception('数据不存在或已删除');
            }
            $model->is_delete = 1;
            $model->save();
            return $this->success([
                'msg' => '处理成功'
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'msg' => $e->getMessage()
            ]);
        }
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = FissionActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->keyword($this->activity_id, ['activity_id' => $this->activity_id])
                ->keyword($this->start_time && $this->end_time, [
                    'AND',
                    ['>', 'created_at', $this->start_time],
                    ['<', 'created_at', $this->end_time]
                ]);
            switch ($this->keyword_name) {
                case 'select_name':
                    $query->keyword($this->keyword_value, ['like', 'select_name', $this->keyword_value]);
                    break;
                case 'nickname':
                    $subQuery = User::find()->alias('u')->select('id')->where(['like', 'nickname', $this->keyword_value])
                        ->andWhere(['u.mall_id' => \Yii::$app->mall->id]);
                    $query->andWhere(['user_id' => $subQuery]);
                    break;
                case 'user_id':
                    $query->andWhere(['like', 'user_id', $this->keyword_value]);
                    break;
                default:
                    break;
            }

            if ($this->flag == "EXPORT") {
                if ($this->choose_list && count($this->choose_list) > 0) {
                    $query->andWhere(['id' => $this->choose_list]);
                }

                $queueId = CommonExport::handle([
                    'export_class' => 'app\\plugins\\fission\\forms\\mall\\ListExport',
                    'params' => [
                        'query' => $query,
                        'fieldsKeyList' => $this->fields,
                    ]
                ]);
                return $this->success([
                    'queue_id' => $queueId
                ]);
            }

            $activity = $query
                ->orderBy('created_at desc')
                ->with('user')
                ->page($pagination, 20, $this->page)
                ->all();

            $return = $this->getReturn($activity);

            return $this->success([
                'msg' => '',
                'list' => $return,
                'pagination' => $pagination,
                'export_list' => (new ListExport())->fieldsList()
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param FissionActivityLog[] $activity
     * @return array[]
     */
    public function getReturn($activity)
    {
        return array_map(function ($item) {
            /** @var FissionActivityLog $item */
            $activity = \yii\helpers\BaseJson::decode($item->activity);

            $share_number = FissionActivityLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'invite_user_id' => $item->user->id,
                'invite_activity_log_id' => $item->id,
                'activity_id' => $activity['id'],
                'is_delete' => 0
            ])->count();
            $share_number++;

            $rewardLog = FissionRewardLog::find()->select('reward_type,reward,real_reward')->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $item->user->id,
                'activity_log_id' => $item->id,
                'is_delete' => 0
            ])->all();

            $first_status = null;
            $first_number = null;
            $current_level = 0;
            $real_reward = 0;
            $current_reward = '';
            foreach ($rewardLog as $log) {
                /** @var FissionRewardLog $log */
                $r = \yii\helpers\BaseJson::decode($log->reward);
                if ($log->reward_type == 0) {
                    $first_status = $r['status'];
                    $first_number = $log['real_reward'];
                    $current_reward = $r;
                    $real_reward = $log->real_reward;
                }
                if ($log->reward_type > $current_level) {
                    $current_level = $log->reward_type;
                    $current_reward = $r;
                    $real_reward = $log->real_reward;
                }
            }
            $reward = CommonReward::addInfo([
                'real_reward' => $real_reward
                , 'model_id' => $current_reward['model_id']
                , 'attr_id' =>$current_reward['attr_id']
                , 'status' => $current_reward['status']
                , 'send_type' => $current_reward['send_type']
                , 'min_number' => $current_reward['min_number']
                , 'max_number' => $current_reward['max_number']
            ]);

            return [
                'id' => $item['id'],
                'name' => $activity['name'],
                'avatar' => $item->user->userInfo->avatar,
                'nickname' => $item->user->nickname,
                'user_id' => $item->user->id,
                'platform_text' => PlatformConfig::getInstance()->getPlatformText($item->user),
                'platform_icon' => PlatformConfig::getInstance()->getPlatformIcon($item->user),
                'first_status' => $first_status,
                'first_number' => $first_number,
                'share_number' => $share_number,
                'last_share_number' => bcsub($activity['number'], $share_number, 0),
                'current_level' => $current_level,
                'rewards' => $reward,
                'created_at' => $item->created_at,
            ];
        }, $activity);
    }
}