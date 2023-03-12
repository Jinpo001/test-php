<?php
namespace app\plugins\fission\forms\receive\basic;

use app\forms\common\coupon\UserCouponData;
use app\models\Coupon;
use app\plugins\fission\models\FissionCouponRelation;
use app\plugins\fission\models\FissionRewardLog;

class CouponRelation extends UserCouponData
{
    public $coupon;
    public $rewardLog;

    public function __construct(Coupon $coupon, FissionRewardLog $rewardLog)
    {
        $this->coupon = $coupon;
        $this->rewardLog = $rewardLog;
    }

    public function save()
    {
        if ($this->check($this->coupon)) {
            $this->coupon->updateCount(1, 'sub', $this->coupon->id);
        } else {
            return false;
        }

        $coupon = new FissionCouponRelation();
        $coupon->mall_id = $this->coupon->mall_id;
        $coupon->activity_log_id = $this->rewardLog->activity_log_id;
        $coupon->reward_log_id = $this->rewardLog->id;
        $coupon->user_coupon_id = $this->userCoupon->id;
        return $coupon->save();
    }
}
