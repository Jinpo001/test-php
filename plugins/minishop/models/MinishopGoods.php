<?php

namespace app\plugins\minishop\models;

use Yii;

/**
 * This is the model class for table "{{%minishop_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $apply_status 审核状态0--未审核 1--审核中 2--审核通过 3--审核驳回
 * @property int $status 上下架状态 0--下架 1--上架 2--上架申请中
 * @property int $product_id 小商店上商品id
 * @property string $third_cat 小商店上分类
 * @property string $brand 小商店上品牌
 * @property string $title 小商店上商品标题
 * @property string $price 小商店上商品价格
 * @property int $stock 小商店上商品库存
 * @property string $goods_info 上传时商品信息
 * @property string $product_info 小商店上商品信息
 * @property string $desc 小商店上商品详情
 * @property string $audit_info 审核结果
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class MinishopGoods extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%minishop_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'apply_status', 'status', 'product_id', 'stock', 'is_delete'], 'integer'],
            [['price'], 'number'],
            [['goods_info', 'product_info', 'desc', 'audit_info', 'created_at'], 'required'],
            [['goods_info', 'product_info', 'desc', 'audit_info'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['third_cat', 'brand', 'title'], 'string', 'max' => 255],
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
            'goods_id' => 'Goods ID',
            'apply_status' => '审核状态0--未审核 1--审核中 2--审核通过 3--审核驳回',
            'status' => '上下架状态 0--下架 1--上架',
            'product_id' => '小商店上商品id',
            'third_cat' => '小商店上分类',
            'brand' => '小商店上品牌',
            'title' => '小商店上商品标题',
            'price' => '小商店上商品价格',
            'stock' => '小商店上商品库存',
            'goods_info' => '上传时商品信息',
            'product_info' => '小商店上商品信息',
            'desc' => '小商店上商品详情',
            'audit_info' => '审核结果',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
