<?php

namespace app\plugins\minishop\models;

use Yii;

/**
 * This is the model class for table "{{%minishop_refund}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $order_refund_id
 * @property int $status
 * @property string $aftersale_infos
 */
class MinishopRefund extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%minishop_refund}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'order_refund_id', 'status', 'aftersale_infos'], 'required'],
            [['mall_id', 'order_id', 'order_refund_id', 'status'], 'integer'],
            [['aftersale_infos'], 'string'],
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
            'order_id' => 'Order ID',
            'order_refund_id' => 'Order Refund ID',
            'status' => 'Status',
            'aftersale_infos' => 'Aftersale Infos',
        ];
    }
}
