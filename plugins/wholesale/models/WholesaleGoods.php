<?php

namespace app\plugins\wholesale\models;

use app\models\GoodsAttr;
use Yii;

/**
 * This is the model class for table "{{%wholesale_goods}}".
 *
 * @property string $id
 * @property int $goods_id
 * @property int $mall_id
 * @property string $wholesale_rules 批发规则
 * @property int $is_delete
 * @property int $type 0默认折扣，1减钱
 * @property int $rise_num 0不设置
 * @property int $rules_status 规则开关，0关闭，1开启
 * @property Goods $goods
 * @property GoodsAttr $attr
 */
class WholesaleGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wholesale_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'mall_id'], 'required'],
            [['goods_id', 'mall_id', 'is_delete', 'type', 'rules_status'], 'integer'],
            [['rise_num'], 'integer', 'max' => 9999999],
            [['wholesale_rules'], 'string', 'max' => 2048],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'mall_id' => 'Mall ID',
            'wholesale_rules' => '批发规则',
            'is_delete' => 'Is Delete',
            'type' => '优惠方式',
            'rise_num' => '起批数',
            'rules_status' => '规则开关',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getAttr()
    {
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }
}
