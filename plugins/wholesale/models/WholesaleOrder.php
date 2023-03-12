<?php

namespace app\plugins\wholesale\models;

use Yii;

/**
 * This is the model class for table "{{%wholesale_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property string $discount
 */
class WholesaleOrder extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wholesale_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id','order_id'], 'integer'],
            [['discount'], 'number'],
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
            'discount' => 'Discount',
        ];
    }
}
