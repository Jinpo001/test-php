<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_platform}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $platform 用户所属平台标识
 * @property string $platform_id 用户所属平台的用户id
 * @property string $password h5平台使用的密码
 * @property string $unionid 微信平台使用的unionid
 * @property int $subscribe 微信平台使用的是否关注
 * @property User $user
 */
class UserPlatform extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_platform}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'subscribe'], 'integer'],
            [['platform', 'platform_id', 'password', 'unionid'], 'string', 'max' => 255],
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
            'platform' => '用户所属平台标识',
            'platform_id' => '用户所属平台的用户id',
            'password' => 'h5平台使用的密码',
            'unionid' => '微信平台使用的unionid',
            'subscribe' => '微信平台使用的是否关注',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
