<?php

namespace app\plugins\teller\models;

use Yii;
use app\models\Store;
use app\models\User;

/**
 * This is the model class for table "{{%cashier}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $number 收银员编号
 * @property int $store_id 门店ID
 * @property int $creator_id 创建者ID
 * @property int $status 状态0.不启用|1.启用
 * @property string $sale_money 销售总金额
 * @property string $push_money 提成总金额
 * @property string $extra_attributes
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class TellerCashier extends \app\models\ModelActiveRecord
{
    const USER_TYPE = 'cashier';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teller_cashier}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'number', 'creator_id', 'user_id'], 'required'],
            [['mall_id', 'mch_id', 'store_id', 'status', 'is_delete', 'creator_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['sale_money', 'push_money'], 'number'],
            [['number'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'number' => '收银员编号',
            'store_id' => '门店ID',
            'status' => '状态0.不启用|1.启用',
            'sale_money' => '销售总金额',
            'push_money' => '提成总金额',
            'creator_id' => '创建者ID',
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

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
