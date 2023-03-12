<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%app_manage}}".
 *
 * @property int $id
 * @property string $name 应用标识
 * @property string $display_name 应用名称
 * @property int $pic_url_type 图标类型
 * @property string $pic_url 应用图标
 * @property string $content 应用简介
 * @property int $is_show 是购买用户是否可见
 * @property string $pay_type 购买方式：online 线上购买 
 * @property string $price 应用售价
 * @property string $detail 应用详情
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class AppManage extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_manage}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pic_url_type', 'is_show', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['detail'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'display_name', 'pic_url', 'content', 'pay_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '应用标识',
            'display_name' => '应用名称',
            'pic_url_type' => '图标类型',
            'pic_url' => '应用图标',
            'content' => '应用简介',
            'is_show' => '是购买用户是否可见',
            'pay_type' => '购买方式：online 线上购买 ',
            'price' => '应用售价',
            'detail' => '应用详情',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
