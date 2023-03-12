<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\basic;

use app\forms\common\coupon\CommonCoupon;
use app\models\Mall;
use app\models\UserCoupon;

class Coupon extends BaseAbstract implements Base
{
    public function exchange(&$message,&$log)
    {
        try {
            $commonCoupon = new CommonCoupon();
            $commonCoupon->mall = Mall::findOne($this->user->mall_id);
            $commonCoupon->user = $this->user;
            $coupon = \app\models\Coupon::findOne([
                'id' => $this->reward['model_id'],
                'is_delete' => 0,
            ]);
            if (!$coupon) {
                throw new \Exception('优惠券不存在');
            }

            $class = new CouponRelation($coupon, $this->rewardLog);
            $desc = sprintf('红包墙兑换(%s)', $this->rewardLog->id);
            $result = $commonCoupon->receive($coupon, $class, $desc, 1) === true;
            if (!$result) {
                throw new \Exception('优惠券领取失败');
            }
            //////////////////////记录userCouponId
            $user_coupon_id = UserCoupon::find()->where(['receive_type' => $desc])->select('id')->column();
            $log->result_id = current($user_coupon_id);
            return true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}
