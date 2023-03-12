<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%license}}".
 *
 * @property int $id
 * @property string $domain 域名
 * @property string $icp_number icp备案号
 * @property string $security_address 公安备案地
 * @property string $security_number 公安备案号
 * @property string $electronic_domain 电子执照链接
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property string $icp_link icp跳转链接
 */
class License extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%license}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['is_delete'], 'integer'],
            [['domain', 'icp_number', 'security_address', 'security_number', 'electronic_domain', 'icp_link'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain' => '域名',
            'icp_number' => 'icp备案号',
            'security_address' => '公安备案地',
            'security_number' => '公安备案号',
            'electronic_domain' => '电子执照链接',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'icp_link' => 'icp跳转链接',
        ];
    }
}
