<?php

namespace app\models;

/**
 * This is the model class for table "{{%recharge}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $pay_price 支付价格
 * @property string $send_price 赠送价格
 * @property int $is_delete 删除
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $send_integral 赠送的积分
 * @property int $send_member_id 赠送的会员
 * @property int $send_type 赠送类型
 * @property string $send_card 赠送卡券
 * @property int $send_coupon 赠送优惠券
 * @property int $lottery_limit 抽奖次数
 */
class Recharge extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public const R_BALANCE = 0b00000001;
    public const R_INTEGRAL = 0b00000010;
    public const R_MEMBER = 0b00000100;
    public const R_COUPON = 0b00001000;
    public const R_CARD = 0b00010000;
    public const R_LOTTERY = 0b00100000;
    public const R_ALL = [
        self::R_BALANCE,
        self::R_INTEGRAL,
        self::R_MEMBER,
        self::R_COUPON,
        self::R_CARD,
        self::R_LOTTERY
    ];

    public static function tableName()
    {
        return '{{%recharge}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'pay_price', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'is_delete', 'send_integral', 'send_member_id', 'lottery_limit', 'send_type'], 'integer'],
            [['pay_price', 'send_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['send_card', 'send_coupon'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'name' => '名称',
            'pay_price' => '支付价格',
            'send_price' => '赠送价格',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Update At',
            'deleted_at' => 'Deleted At',
            'send_integral' => '赠送的积分',
            'send_member_id' => '赠送的会员',
            'send_type' => '赠送类型',
            'send_card' => '赠送卡券',
            'send_coupon' => '赠送优惠券',
            'lottery_limit' => '抽奖次数'
        ];
    }

    public function getMember()
    {
        return $this->hasOne(MallMembers::className(), ['id' => 'send_member_id', 'is_delete' => 'is_delete']);
    }
}
