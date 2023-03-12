<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%city_service}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $platform 所属平台
 * @property string $name 配送名称
 * @property int $distribution_corporation 配送公司 1.顺丰|2.闪送|3.美团配送|4.达达
 * @property string $shop_no 门店编号
 * @property string $data
 * @property string $created_at
 * @property int $is_delete
 * @property string $service_type
 * @property string $plugin 区分插件还是商城
 * @property int $status 状态是否开启
 */
class CityService extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%city_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'distribution_corporation', 'service_type'], 'required'],
            [['mall_id', 'distribution_corporation', 'is_delete', 'status'], 'integer'],
            [['data'], 'string'],
            [['created_at'], 'safe'],
            [['platform', 'name', 'shop_no', 'service_type', 'plugin'], 'string', 'max' => 255],
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
            'platform' => '所属平台',
            'name' => '配送名称',
            'distribution_corporation' => '配送公司 1.顺丰|2.闪送|3.美团配送|4.达达',
            'shop_no' => '门店编号',
            'data' => 'Data',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'service_type' => 'Service Type',
            'plugin' => '区分插件还是商城',
            'status' => '状态是否开启',
        ];
    }
}