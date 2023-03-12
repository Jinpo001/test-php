<?php

namespace app\plugins\fission\models;

use Yii;

/**
 * This is the model class for table "{{%fission_activity}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 活动名称
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property int $style 红包墙的样式
 * @property int $number 红包数量2～100
 * @property string $app_share_title 自定义分享标题
 * @property string $app_share_pic 自定义分享图片
 * @property string $rule_title 规则标题
 * @property string $rule_content 规则内容
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $expire_time 赠品失效天数
 * @property int $status 是否上架
 * @property FissionActivityReward[] $rewards
 * @property FissionActivityLog[] $logs
 */
class FissionActivity extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fission_activity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'start_time', 'end_time', 'rule_content', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'style', 'number', 'is_delete', 'expire_time', 'status'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['app_share_pic', 'rule_content'], 'string'],
            [['name', 'app_share_title', 'rule_title'], 'string', 'max' => 255],
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
            'name' => '活动名称',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'style' => '红包墙的样式',
            'number' => '红包数量2～100',
            'app_share_title' => '自定义分享标题',
            'app_share_pic' => '自定义分享图片',
            'rule_title' => '规则标题',
            'rule_content' => '规则内容',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'expire_time' => '赠品失效天数',
            'status' => '是否上架',
        ];
    }

    public function getRewards()
    {
        return $this->hasMany(FissionActivityReward::className(), ['activity_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getLogs()
    {
        return $this->hasMany(FissionActivityLog::className(), ['activity_id' => 'id']);
    }
}
