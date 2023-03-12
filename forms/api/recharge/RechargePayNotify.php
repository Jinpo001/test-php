<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\recharge;


use app\core\payment\PaymentNotify;
use app\forms\common\card\CommonCard;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\coupon\CouponMallRelation;
use app\forms\mall\recharge\RechargePageForm;
use app\models\GoodsCards;
use app\models\Mall;
use app\models\MallMembers;
use app\models\Recharge;
use app\models\RechargeOrders;
use app\models\User;

class RechargePayNotify extends PaymentNotify
{
    private $desc = '';

    public function notify($paymentOrder)
    {
        try {
            /* @var RechargeOrders $order */
            $order = RechargeOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$order) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            //商城
            \Yii::$app->setMall(Mall::findOne(['id' => $order->mall_id]));

            if ($order->pay_type != 1) {
                throw new \Exception('必须使用微信支付');
            }

            $order->is_pay = 1;
            $order->pay_time = date('Y-m-d H:i:s', time());
            $res = $order->save();

            if (!$res) {
                throw new \Exception('充值订单支付状态更新失败');
            }

            $user = User::findOne($order->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }


            $this->sendData($user, $order);
            
        } catch (\Exception $e) {
            \Yii::error($e);
            throw $e;
        }
    }

    protected function sendData(User $user, RechargeOrders $order)
    {
        $this->desc = '';
        $order->send_type & Recharge::R_INTEGRAL && $this->sendIntegral($user, $order);
        $order->send_type & Recharge::R_MEMBER && $this->sendMember($user, $order);
        $order->send_type & Recharge::R_COUPON && $this->sendCoupon($user, $order);
        $order->send_type & Recharge::R_CARD && $this->sendCard($user, $order);
        $order->send_type & Recharge::R_LOTTERY && $order->lottery_limit && $this->sendLotteryLimit($user, $order->lottery_limit);
        $this->sendBalance($user, $order);
    }

    private function setText($text)
    {
        $this->desc .= sprintf('，%s', $text);
    }

    protected function sendLotteryLimit($user, $limit)
    {
        $desc = '';
        $setting = (new RechargePageForm())->getSetting();
        if ($setting['is_lottery_open']) {
            $lottery_type = $setting['lottery_type'];
            switch ($lottery_type) {
                case 'pond':
                    $className = '\app\plugins\pond\models\PondBout';
                    $pluginName = (new \app\plugins\pond\Plugin())->getDisplayName();
                    break;
                case 'scratch':
                    $className = '\app\plugins\scratch\models\ScratchBout';
                    $pluginName = (new \app\plugins\scratch\Plugin())->getDisplayName();
                    break;
                default:
                    return false;
            }
            $sql = sprintf(
                'insert into %s(mall_id, user_id, bout, updated_at) VALUES(%s, %s, %s, "%s") ON DUPLICATE KEY UPDATE bout = %d + bout',
                $className::tableName(),
                \Yii::$app->mall->id,
                $user->id,
                $limit,
                date('Y-m-d H:i:s'),
                $limit
            );
            $hot = \Yii::$app->db->createCommand($sql)->execute();
            if ($hot) {
                $desc .= sprintf('%s赠送次数：%s次', $pluginName, $limit);
            } else {
                $desc .= '赠送抽奖次数失败';
            }
        }
        $this->setText($desc);
    }

    protected function sendCard($user, $order)
    {
        try {
            $desc = '';
            $cards = \yii\helpers\BaseJson::decode($order->send_card);
            if (is_array($cards)) {
                foreach ($cards as $card) {
                    $goodsCards = GoodsCards::find()->where([
                        'id' => $card['id'],
                        'is_delete' => 0,
                    ])->one();
                    if ($goodsCards) {
                        $class = new CommonCard();
                        $class->user = $user;
                        $class->user_id = $user->id;
                        /** @var GoodsCards $goodsCards */
                        $userCard = $class->receive($goodsCards, 0, 0, '余额充值赠送', $card['num']);
                        if ($userCard) {
                            $desc .= sprintf(' 赠送卡券：%sx%s', $goodsCards->name, $card['num']);
                        } else {
                            $desc .= '赠送卡券失败：领取失败';
                        }
                    } else {
                        $desc .= '赠送卡券失败：不存在';
                    }
                }
            }
            $this->setText($desc);
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            $this->setText($desc);
        }
    }

    protected function sendCoupon($user, $order)
    {
        try {
            $desc = '';
            $coupons = \yii\helpers\BaseJson::decode($order->send_coupon);
            if (is_array($coupons)) {
                foreach ($coupons as $coupon) {
                    $commonCoupon = new CommonCoupon();
                    $commonCoupon->mall = Mall::findOne($user->mall_id);
                    $commonCoupon->user = $user;
                    $class = \app\models\Coupon::findOne([
                        'id' => $coupon['coupon_id'],
                        'is_delete' => 0,
                    ]);
                    if ($class) {
                        $relation = new CouponMallRelation($class, $order->id, CouponMallRelation::TYPE_BALANCE);
                        $result = $commonCoupon->receive($class, $relation, '余额充值赠送', $coupon['send_num']) === true;
                        if ($result) {
                            $desc .= sprintf(' 赠送优惠券：%sx%s', $class->name, $coupon['send_num']);
                        } else {
                            $desc .= '赠送优惠券失败：领取失败';
                        }
                    } else {
                        $desc .= '赠送优惠券失败：不存在';
                    }
                }
            }
            $this->setText($desc);
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            $this->setText($desc);
        }
    }

    protected function sendMember($user, $order)
    {
        $desc = '';
        if (!empty($order->send_member_id)) {
            $mallMembersModel = MallMembers::findOne([
                'id' => $order->send_member_id,
                'status' => 1,
                'is_delete' => 0,
            ]);
            if ($mallMembersModel) {
                if ($user->identity->member_level >= $mallMembersModel->level) {
                    $desc = '赠送会员失败：用户会员等级高于或等于赠送等级';
                } else {
                    $desc = sprintf('赠送会员成功：%s(%s)', $mallMembersModel->name, $mallMembersModel->id);
                    $user->identity->member_level = $mallMembersModel->level;
                    $user->identity->save();
                }
            } else {
                $desc = '赠送会员失败：会员状态异常，请查看会员是否启用';
            }
        }
        $this->setText($desc);
    }

    protected function sendBalance($user, $order)
    {
        $desc = '充值余额：' . $order->pay_price . '元';
        if ($order->send_type & Recharge::R_BALANCE) {
            $price = (float)($order->pay_price + $order->send_price);
            $desc .= sprintf('，赠送：%s元', $order->send_price);
        } else {
            $price = (float)($order->pay_price);
        }
        \Yii::$app->currency->setUser($user)->balance->add(
            $price,
            $desc . $this->desc,
            \Yii::$app->serializer->encode($order->attributes),
            $order->order_no
        );
    }

    protected function sendIntegral($user, $order)
    {
        \Yii::$app->currency->setUser($user)->integral->add(
            $order->send_integral,
            "余额充值,赠送积分{$order->send_integral}",
            \Yii::$app->serializer->encode($order->attributes),
            $order->order_no
        );
        $this->setText(sprintf('赠送：%s积分', $order->send_integral));
    }
}
