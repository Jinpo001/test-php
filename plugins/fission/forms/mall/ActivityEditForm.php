<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/16
 * Time: 3:32 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivity;
use app\plugins\fission\models\FissionActivityReward;
use yii\helpers\Json;

class ActivityEditForm extends Model
{
    public $id;
    public $name;
    public $start_time;
    public $end_time;
    public $style;
    public $number;
    public $app_share_title;
    public $app_share_pic;
    public $rule_title;
    public $rule_content;
    public $reward_status;
    public $reward_coupon_id;
    public $reward_min_number;
    public $reward_max_number;
    public $reward_send_type;
    public $level_list;
    public $expire_time;

    public function rules()
    {
        $string = ['name', 'start_time', 'end_time', 'app_share_title', 'app_share_pic', 'rule_title', 'rule_content',
            'reward_status', 'reward_send_type'];
        return [
            [$string, 'trim'],
            [$string, 'string'],
            [['name', 'start_time', 'end_time', 'number', 'reward_status'], 'required'],
            [['id', 'style', 'reward_coupon_id'], 'integer'],
            [['expire_time'], 'integer', 'min' => 0, 'max' => 999999],
            [['reward_min_number', 'reward_max_number'], 'number', 'min' => 0, 'max' => 999999],
            ['number', 'integer', 'min' => 2, 'max' => 100],
            ['level_list', 'safe'],
            ['reward_status', 'in', 'range' => ['cash', 'balance', 'coupon']],
            ['style', 'in', 'range' => [1, 2]],
            ['reward_send_type', 'in', 'range' => ['random', 'average']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '活动名称',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'style' => '红包墙的样式',
            'number' => '红包数量2～100',
            'app_share_title' => '自定义分享标题',
            'app_share_pic' => '自定义分享图片',
            'rule_title' => '规则标题',
            'rule_content' => '规则内容',
            'reward_status' => '红包种类',
            'reward_coupon_id' => '优惠券id',
            'reward_min_number' => $this->reward_send_type == 'average' ? '红包金额固定金额' : '红包金额最低金额',
            'reward_max_number' => '红包金额最大值',
            'reward_send_type' => '红包金额发放方式',
            'level_list' => '关卡列表',
            'expire_time' => '赠品失效时间'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (strtotime($this->start_time) > strtotime($this->end_time)) {
                throw new \Exception('开始时间需要小于结束时间');
            }
            $check = FissionActivity::find()
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
                ->keyword($this->id, ['!=', 'id', $this->id])
                ->andWhere([
                    'or',
                    [
                        'and',
                        ['>=', 'start_time', $this->start_time],
                        ['<', 'start_time', $this->end_time],
                    ],
                    [
                        'and',
                        ['>', 'end_time', $this->start_time],
                        ['<=', 'end_time', $this->end_time],
                    ],
                    [
                        'and',
                        [
                            '<=', 'start_time', $this->start_time
                        ],
                        [
                            '>=', 'end_time', $this->end_time
                        ]
                    ]
                ])->exists();
            if ($check) {
                throw new \Exception('该时间段已有活动,请修改活动时间日期');
            }
            /** @var FissionActivity $activity */
            $activity = FissionActivity::find()->with(['rewards'])
                ->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
                ->one();
            if (!$activity) {
                $activity = new FissionActivity();
                $activity->mall_id = \Yii::$app->mall->id;
                $activity->is_delete = 0;
                $activity->status = 0;
            }
            // 进行中的活动无法编辑开始时间和红包个数
            if (!$activity->start_time || strtotime($activity->start_time) > time()) {
                $activity->start_time = $this->start_time;
                $activity->number = $this->number;
            }
            $activity->end_time = $this->end_time;
            $activity->name = $this->name;
            $activity->style = $this->style;
            $activity->app_share_title = $this->app_share_title;
            $activity->app_share_pic = $this->app_share_pic;
            $activity->rule_title = $this->rule_title;
            $activity->rule_content = $this->rule_content;
            $activity->expire_time = $this->expire_time;
            if (!$activity->save()) {
                throw new \Exception($this->getErrorMsg($activity));
            }
            if (!$this->level_list) {
                throw new \Exception('至少需要填写一个关卡');
            }
            $levelList = Json::decode($this->level_list, true);
            if (empty($levelList)) {
                throw new \Exception('至少需要填写一个关卡');
            }
            array_unshift($levelList, [
                'status' => $this->reward_status,
                'people_number' => 0,
                'model_id' => $this->reward_coupon_id,
                'exchange_type' => $this->reward_status == 'cash' ? 'offline' : 'online',
                'min_number' => $this->reward_min_number,
                'max_number' => $this->reward_max_number,
                'send_type' => in_array($this->reward_status, ['cash', 'balance'])
                    ? $this->reward_send_type : 'average',
                'level' => 'main',
            ]);
            $rewards = [];
            if ($activity->rewards) {
                foreach ($activity->rewards as $reward) {
                    $reward->is_delete = 1;
                    $rewards[$reward->type . '-' . $reward->level] = $reward;
                }
            }
            $restPeople = 1;
            foreach ($levelList as $key => $item) {
                $msg = $this->msg($key);
                // 最后一个关卡邀请人数等于红包总数
                if ($key > 0 && $key == count($levelList) - 1) {
                    $item['people_number'] = $this->number - $restPeople;
                }
                $restPeople += $item['people_number'];
                if ($restPeople > $this->number) {
                    throw new \Exception($msg . '邀请人数超过上限人数' . $this->number);
                }
                if ($key > 0) {
                    // 添加关卡缺少固定的字段
                    $item['level'] = 'main';
                    // 关卡金额、红包、积分发放方式固定为average
                    $item['send_type'] = 'average';
                    // 现金只有线下兑换
                    $item['exchange_type'] = $item['status'] == 'cash' ? 'offline' : $item['exchange_type'];
                    if ($item['people_number'] <= 0) {
                        throw new \Exception($msg . '邀请人数必须大于0');
                    }
                }
                $form = $this->formCheck($item, $key);
                $reward = $this->saveReward($form, $rewards, $key, 'main', $activity);
                if ($key > 0 && in_array($reward->status, ['goods', 'card', 'coupon'])) {
                    if (!isset($item['second']) || !$item['second'] || empty($item['second'])) {
                        throw new \Exception($msg . '需要设置次要奖励');
                    }
                    $form = $this->formCheck([
                        'status' => $item['second']['status'],
                        'people_number' => $item['people_number'],
                        'model_id' => 0,
                        'exchange_type' => $item['second']['status'] == 'cash' ? 'offline' : 'online',
                        'min_number' => $item['second']['min_number'],
                        'max_number' => $item['second']['max_number'],
                        'send_type' => $item['second']['send_type'],
                        'level' => 'secondary',
                    ], $key);
                    $reward = $this->saveReward($form, $rewards, $key, 'secondary', $activity);
                }
            }
            foreach ($rewards as $reward) {
                if (!$reward->save()) {
                    throw new \Exception($this->getErrorMsg($rewards));
                }
            }
            $transaction->commit();
            return $this->success(['msg' => '保存成功']);
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return $this->fail([
                'msg' => $exception->getMessage(),
                'error' => $exception
            ]);
        }
    }

    public function msg($key)
    {
        return $key > 0 ? '关卡' . $key : '';
    }

    public function formCheck($item, $key)
    {
        $msg = $this->msg($key);
        $form = new RewardForm();
        $form->scenario = $item['status'];
        $form->attributes = $item;
        if (!$form->check()) {
            throw new \Exception($msg . $form->errorMsg);
        }
        return $form;
    }

    /**
     * @param RewardForm $form
     * @param FissionActivityReward[] $rewards
     * @param string $key
     * @param FissionActivity $activity
     * @return FissionActivityReward
     * @throws \Exception
     */
    public function saveReward($form, &$rewards, $key, $level, $activity)
    {
        if (!isset($rewards[$key . '-' . $level])) {
            $reward = new FissionActivityReward();
            $reward->activity_id = $activity->id;
            $reward->mall_id = $activity->mall_id;
            $reward->type = $key;
        } else {
            $reward = $rewards[$key . '-' . $level];
        }
        $reward->is_delete = 0;
        $reward->attributes = $form->attributes;
        $rewards[$key . '-' . $level] = $reward;
        return $reward;
    }
}
