<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%app_order}}".
 *
 * @property int $id
 * @property int $user_id 账号ID
 * @property string $nickname 账号名称
 * @property string $name 应用标识
 * @property string $app_name 应用名称
 * @property string $order_no 订单号
 * @property string $pay_price 支付价格
 * @property string $pay_type 支付方式
 * @property int $is_pay 是否支付
 * @property string $pay_time 支付时间
 * @property string $extra_attributes
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property string $out_trade_no 商户订单号
 */
class AppOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%app_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'is_pay', 'is_delete'], 'integer'],
            [['pay_price'], 'number'],
            [['pay_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['extra_attributes'], 'string'],
            [['nickname', 'name', 'app_name', 'order_no', 'pay_type', 'out_trade_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '账号ID',
            'nickname' => '账号名称',
            'name' => '应用标识',
            'app_name' => '应用名称',
            'order_no' => '订单号',
            'pay_price' => '支付价格',
            'pay_type' => '支付方式',
            'is_pay' => '是否支付',
            'pay_time' => '支付时间',
            'extra_attributes' => 'Extra Attributes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'out_trade_no' => '商户订单号',
        ];
    }
}