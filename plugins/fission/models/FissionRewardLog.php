<?php

namespace app\plugins\fission\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%fission_reward_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $reward_type 关卡
 * @property int $reward_id 奖励id 0第一次奖励
 * @property int $activity_log_id 活动id
 * @property int $is_exchange 是否兑换
 * @property string $reward 关卡记录
 * @property int $expire_time 赠品失效天数
 * @property string $real_reward 兑换真实奖励 随机奖品使用的
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 * @property int $result_id
 * @property string $token 订单token
 * @property FissionActivityLog $activityLog
 * @property User $user
 */
class FissionRewardLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fission_reward_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'reward_type', 'reward_id', 'activity_log_id', 'real_reward'], 'required'],
            [['mall_id', 'user_id', 'reward_type', 'reward_id', 'activity_log_id', 'is_exchange', 'expire_time', 'is_delete', 'result_id'], 'integer'],
            [['reward'], 'string'],
            [['real_reward'], 'number'],
            [['created_at', 'deleted_at'], 'safe'],
            [['token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'user_id' => 'User ID',
            'reward_type' => '关卡',
            'reward_id' => '奖励id 0第一次奖励',
            'activity_log_id' => '活动id',
            'is_exchange' => '是否兑换',
            'reward' => '关卡记录',
            'expire_time' => '赠品失效天数',
            'real_reward' => '兑换真实奖励 随机奖品使用的',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'result_id' => '领取后的id',
            'token' => '订单token',
        ];
    }

    public function getActivityLog()
    {
        return $this->hasOne(FissionActivityLog::className(), ['id' => 'activity_log_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
