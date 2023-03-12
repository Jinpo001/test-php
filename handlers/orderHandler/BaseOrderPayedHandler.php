<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/13
 * Time: 17:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers\orderHandler;


use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use app\core\mail\SendMail;
use app\core\sms\Sms;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonBuyPrompt;
use app\forms\common\card\CommonSend;
use app\forms\common\coupon\CommonCouponAutoSend;
use app\forms\common\coupon\CommonCouponGoodsSend;
use app\forms\common\goods\CommonGoods;
use app\forms\common\message\MessageService;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\share\CommonShare;
use app\forms\common\template\TemplateList;
use app\forms\common\template\order_pay_template\OrderPayInfo;
use app\models\CouponAutoSend;
use app\models\OrderPayResult;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\plugins\mch\forms\common\MchSuccessInfo;
use app\plugins\mch\models\Mch;

/**
 * @property User $user
 */
abstract class BaseOrderPayedHandler extends BaseOrderHandler
{
    public $user;

    /**
     * @return $this
     * 保存支付完成处理结果
     */
    protected function saveResult()
    {
        $cardList = $this->sendCard();
        $userCouponList = $this->sendCoupon();
        $userCouponList = array_merge($userCouponList, $this->sendCouponUse(), $this->sendCouponByGoods());
        $data = [
            'card_list' => $cardList,
            'user_coupon_list' => $userCouponList,
        ];
        $orderPayResult = new OrderPayResult();
        $orderPayResult->order_id = $this->event->order->id;
        $orderPayResult->data = $orderPayResult->encodeData($data);
        $orderPayResult->save();
        return $this;
    }

    /**
     * @return array
     * 向用户发送商品卡券
     */
    protected function sendCard()
    {
        try {
            $cardSendForm = new CommonSend();
            $cardSendForm->mall_id = \Yii::$app->mall->id;
            $cardSendForm->user_id = $this->event->order->user_id;
            $cardSendForm->order_id = $this->event->order->id;
            /** @var UserCard[] $userCardList */
            $userCardList = $cardSendForm->save();
            $cardList = [];
            foreach ($userCardList as $userCard) {
                $cardList[] = $userCard->attributes;
            }
        } catch (\Exception $exception) {
            \Yii::error('卡券发放失败: ' . $exception->getMessage());
            $cardList = [];
        }
        return $cardList;
    }

    /**
     * @return array
     * 向用户发送优惠券（自动发送方案--订单支付成功发送优惠券）
     */
    protected function sendCoupon()
    {
        try {
            $couponSendForm = new CommonCouponAutoSend();
            $couponSendForm->event = CouponAutoSend::PAY;
            $couponSendForm->user = $this->user;
            $couponSendForm->mall = $this->mall;
            $userCouponList = $couponSendForm->send();
        } catch (\Exception $exception) {
            \Yii::error('优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
    }

    /**
     * @return array
     * 向用户发送优惠券（购买商品赠送--订单支付成功发送优惠券）
     */
    protected function sendCouponByGoods()
    {
        try {
            $couponSendForm = new CommonCouponGoodsSend();
            $couponSendForm->user = $this->user;
            $couponSendForm->mall = $this->mall;
            $couponSendForm->order_id = $this->event->order->id;
            $userCouponList = $couponSendForm->send();
            \Yii::warning('购买商品赠送优惠券发放数据');
            \Yii::warning($userCouponList);
        } catch (\Exception $exception) {
            \Yii::error('商品赠送优惠券发放失败: ' . $exception->getMessage());
            $userCouponList = [];
        }
        return $userCouponList;
    }

    /**
     * 优惠券自动赠送规则
     * @return array
     */
    protected function sendCouponUse()
    {
        try {
            if ($this->event->order->use_user_coupon_id && $userCoupon = UserCoupon::findOne($this->event->order->use_user_coupon_id)) {
                $couponUseSendForm = new CommonCouponGoodsSend();
                $couponUseSendForm->user = $this->user;
                $couponUseSendForm->mall = $this->mall;
                $couponUseSendForm->order_id = $this->event->order->id;
                $couponData = $couponUseSendForm->useSend($userCoupon->coupon_id);
                return [$couponData];
            }
            throw new \Exception('订单或优惠券问题');
        } catch (\Exception $e) {
            \Yii::error('优惠券购赠失败：' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return $this
     * 短信发送--新订单通知
     */
    protected function sendSms()
    {
        try {
            if ($this->orderConfig->is_sms != 1) {
                throw new \Exception('未开启短信提醒');
            }
            $sms = new Sms(['mch_id' => $this->event->order->mch_id]);
            $smsConfig = CommonAppConfig::getSmsConfig($this->event->order->mch_id);
            if ($smsConfig['status'] == 1 && $smsConfig['mobile_list']) {
                $sms->sendOrderMessage($smsConfig['mobile_list'], $this->event->order->order_no);
            }
        } catch (NoGatewayAvailableException $exception) {
            \Yii::error('短信发送: ' . $exception->getExceptions());
        } catch (\Exception $exception) {
            \Yii::error('短信发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 邮件发送--新订单通知
     */
    protected function sendMail()
    {
        // 发送邮件
        try {
            if ($this->orderConfig->is_mail != 1) {
                throw new \Exception('未开启邮件提醒');
            }
            $mailer = new SendMail();
            $mailer->mall = $this->mall;
            $mailer->mch_id = $this->event->order->mch_id;
            $mailer->order = $this->event->order;
            $mailer->orderPayMsg();
        } catch (\Exception $exception) {
            \Yii::error('邮件发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 首次付款成为下级
     */
    protected function becomeJuniorByFirstPay()
    {
        try {
            $commonShare = new CommonShare();
            $commonShare->mall = $this->mall;
            $commonShare->user = $this->user;
            $commonShare->bindParent($this->user->userInfo->temp_parent_id, 3);
        } catch (\Exception $exception) {
            \Yii::error('首次付款成为下级：' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 下单成为分销商
     */
    protected function becomeShare()
    {
        try {
            $commonShare = new CommonShare();
            $commonShare->mall = $this->mall;
            $commonShare->becomeShareByAuto($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('下单成为分销商: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 通过小程序模板消息发送给用户支付成功通知
     */
    protected function sendTemplate()
    {
        try {
            $order = $this->event->order;
            $goodsName = '';
            foreach ($order->detail as $orderDetail) {
                $goodsName .= $orderDetail->goods->name;
            }
            TemplateList::getInstance()->getTemplateClass(OrderPayInfo::TPL_NAME)->send([
                'order_no' => $order->order_no,
                'pay_time' => $order->pay_time,
                'price' => $order->total_pay_price,
                'goodsName' => $goodsName,
                'user' => $order->user,
                'page' => 'pages/order/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }

    /**
     * @return $this
     * 通过公众号向商家发送公众号消息
     */
    protected function sendMpTemplate()
    {
        if ($this->event->order->mch_id > 0) {
            \Yii::warning('多商户订单无需向平台管理员发送模板消息');
            return $this;
        }

        $goodsName = '';
        foreach ($this->event->order->detail as $detail) {
            $goodsName .= $detail->goods->name;
        }
        try {
            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'newOrderTpl';
            $tplMsg->params = [
                'sign' => $this->event->order->sign,
                'goods' => $goodsName,
                'time' => date('Y-m-d H:i:s'),
                'user' => $this->user->nickname,
                'total_pay_price' => $this->event->order->total_pay_price,
            ];
            $tplMsg->sendTemplate(new MpTplMsgDSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
        return $this;
    }


    protected function sendTemplateMsgToMch()
    {
        if ($this->event->order->mch_id == 0) {
            return $this;
        }
        \Yii::warning('多商户发送商家模板消息');

        try {
            /** @var Mch $mch */
            $mch = Mch::find()->where(['id' => $this->event->order->mch_id])->with('user')->one();
            if (!$mch) {
                throw new \Exception('商户不存在,商户审核订阅消息发送失败');
            }

            if (!$mch->user) {
                throw new \Exception('用户不存在,商户审核订阅消息发送失败');
            }

            TemplateList::getInstance()->getTemplateClass(MchSuccessInfo::TPL_NAME)->send([
                'order_no' => $this->event->order->order_no,
                'price' => $this->event->order->total_pay_price,
                'time' => $this->event->order->created_at,
                'remark' => $this->event->order->remark ? '备注:' . $this->event->order->remark : '有用户下单,请尽快处理',
                'user' => $mch->user,
                'page' => 'plugins/mch/mch/order/order?mch_id=' . $this->event->order->mch_id
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$mch->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $mch->user;
            $messageService->content = [
                'mch_id' => $this->event->order->mch_id,
                'args' => [\Yii::$app->mall->name]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($mch->user);
            $messageService->tplKey = OrderPayInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }

        return $this;
    }

    /**
     * @return $this
     * 向小程序端发送购买提示消息
     */
    protected function sendBuyPrompt()
    {
        if (count($this->event->order->detail) > 0) {
            $details = $this->event->order->detail;
            $goods = $details[0]->goods;
            $goodsId = $goods->id;
            $goodsName = $goods->name;
        } else {
            $goodsId = 0;
            $goodsName = '';
        }
        try {
            $buy_data = new CommonBuyPrompt();
            $buy_data->nickname = $this->user->nickname;
            $buy_data->avatar = $this->user->userInfo->avatar;
            $buy_data->url = '/pages/goods/goods/id=' . $goodsId;
            $buy_data->goods_name = $goodsName;
            $buy_data->set();
        } catch (\Exception $exception) {
            \Yii::error('首页购买提示失败: ' . $exception->getMessage());
        }
        return $this;
    }

    protected function setGoods()
    {
        try {
            CommonGoods::getCommon()->setGoodsPayment($this->event->order, 'add');
            CommonGoods::getCommon()->setGoodsSales($this->event->order);
        } catch (\Exception $exception) {
            \Yii::error('商品支付信息设置');
            \Yii::error($exception);
        }
        return $this;
    }

    /**
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser()
    {
        try {
            \Yii::warning('----消息发送提醒----');
            $order = $this->event->order;
            if (!$order->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $order->user;
            $messageService->content = [
                'mch_id' => $order->mch_id,
                'args' => [\Yii::$app->mall->name]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($order->user);
            $messageService->tplKey = OrderPayInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
