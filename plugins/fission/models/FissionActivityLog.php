<?php

namespace app\plugins\fission\models;

use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%fission_activity_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $activity_id
 * @property int $invite_activity_log_id 邀请人id
 * @property int $invite_user_id 邀请人id
 * @property string $rewards 关卡奖励
 * @property string $activity 活动
 * @property string $select_name 活动
 * @property int $is_delete
 * @property string $created_at
 * @property string $deleted_at
 * @property FissionRewardLog[] $rewardLog
 * @property FissionRewardLog $first
 */
class FissionActivityLog extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fission_activity_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'activity_id'], 'required'],
            [['mall_id', 'user_id', 'activity_id', 'invite_user_id', 'is_delete', 'invite_activity_log_id'], 'integer'],
            [['rewards', 'activity'], 'string'],
            [['created_at', 'deleted_at'], 'safe'],
            [['select_name'], 'string', 'max' => 255],
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
            'activity_id' => 'Activity ID',
            'invite_user_id' => '邀请人id',
            'invite_activity_log_id' => '参与邀请id',
            'rewards' => '关卡奖励',
            'activity' => '活动',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'select_name' => '搜索使用'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->andWhere(['is_delete' => 0]);
    }

    public function getInvite()
    {
        return $this->hasOne(User::className(), ['id' => 'invite_user_id'])->andWhere(['is_delete' => 0]);
    }

    public function getRewardLog()
    {
        return $this->hasMany(FissionRewardLog::className(), ['activity_log_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getFirst()
    {
        return $this->hasOne(FissionRewardLog::className(), ['activity_log_id' => 'id'])->andWhere(['reward_type' => 0]);
    }
}
