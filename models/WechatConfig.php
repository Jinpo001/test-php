<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%wechat_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $appid
 * @property string $appsecret
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $name
 * @property string $logo
 * @property string $qrcode
 * @property int $version 数据版本1--第一版 2--第二版
 */
class WechatConfig extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete', 'version'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['appid', 'appsecret', 'name', 'logo', 'qrcode'], 'string', 'max' => 255],
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
            'appid' => 'Appid',
            'appsecret' => 'Appsecret',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'name' => 'Name',
            'logo' => 'Logo',
            'qrcode' => 'Qrcode',
            'version' => '数据版本1--第一版 2--第二版',
        ];
    }
}
