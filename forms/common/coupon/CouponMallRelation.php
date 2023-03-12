<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 14:28
 */

namespace app\forms\common\coupon;

use app\models\Coupon;
use app\models\User;

/**
 * @property User $user
 */
class CouponMallRelation extends UserCouponData
{
    public $coupon;
    public $user;
    public $userCoupon;
    public $order_id;
    public $type;

    public const TYPE_COUPON = 'use';
    public const TYPE_BALANCE = 'balance';

    public function __construct(Coupon $coupon, $order_id = 0, $type = '')
    {
        $this->coupon = $coupon;
        $this->order_id = $order_id;
        $this->type = $type;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub');
        } else {
            return false;
        }
        $CouponMallRelation = new \app\models\CouponMallRelation();
        $CouponMallRelation->mall_id = $this->coupon->mall_id;
        $CouponMallRelation->user_coupon_id = $this->userCoupon->id;
        $CouponMallRelation->is_delete = 0;
        $CouponMallRelation->order_id = $this->order_id;
        $CouponMallRelation->type = $this->type;
        return $CouponMallRelation->save();
    }
}
