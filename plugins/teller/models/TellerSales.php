<?php

namespace app\plugins\teller\models;

use Yii;
use app\models\Store;
use app\models\User;

/**
 * This is the model class for table "{{%teller_sales}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $number 导购员编号
 * @property string $name 姓名
 * @property string $head_url 头像
 * @property string $mobile 电话
 * @property int $store_id 门店ID
 * @property int $creator_id 创建者ID
 * @property int $status 状态0.不启用|1.启用
 * @property string $push_money 提成总金额
 * @property string $sale_money 销售总金额
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class TellerSales extends \app\models\ModelActiveRecord
{
    const USER_TYPE = 'sales';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teller_sales}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'number', 'name', 'mobile', 'creator_id'], 'required'],
            [['mall_id', 'mch_id', 'store_id', 'creator_id', 'status', 'is_delete'], 'integer'],
            [['push_money', 'sale_money'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['number', 'name', 'mobile', 'head_url'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'number' => '导购员编号',
            'name' => '姓名',
            'head_url' => '头像',
            'mobile' => '电话',
            'store_id' => '门店ID',
            'creator_id' => '创建者ID',
            'status' => '状态0.不启用|1.启用',
            'push_money' => '提成金额',
            'sale_money' => '销售金额',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
    }
}
