<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/22
 * Time: 11:27 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\forms\api\order\OrderException;
use app\models\Address;
use app\models\Order;
use app\models\User;
use app\plugins\fission\forms\api\OrderGoodsAttr;
use app\plugins\fission\models\FissionActivityReward;
use app\plugins\fission\models\FissionRewardLog;
use app\plugins\fission\Plugin;
use yii\helpers\Json;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $this->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(false)
            ->setEnableAddressEnable(false)
            ->setEnablePriceEnable(false)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes(['cash'])
            ->setSign((new Plugin())->getName());
        return $this;
    }

    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        // 兑换赠品不处理库存
        return true;
    }

    public function checkGoodsStock($goodsList)
    {
        // 兑换赠品不处理库存
        return true;
    }

    /**
     * @var FissionRewardLog $rewardLog
     */
    public $rewardLog;

    public function checkGoods($goods, $item)
    {
        $rewardLogId = $this->form_data['list'][0]['reward_log_id'];
        /** @var FissionRewardLog $rewardLog */
        $rewardLog = FissionRewardLog::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'user_id' => $this->getUser()->id,
                'id' => $rewardLogId
            ])->one();
        if (!$rewardLog) {
            throw new OrderException('奖励不存在或已被删除');
        }
        if ($rewardLog->is_exchange == 1) {
            throw new OrderException('奖励已被领取，请不要重复领取');
        }
        if ($rewardLog->result_id) {
            throw new OrderException('奖励已被领取，请不要重复领取');
        }
        $reward = new FissionActivityReward();
        $reward->attributes = Json::decode($rewardLog->reward, true);
        if (
            $reward->status != 'goods'
            || $goods->id != $reward->model_id
            || $item['goods_attr_id'] != $reward->attr_id
        ) {
            throw new OrderException('奖励不存在或已被删除');
        }
        if ($reward->exchange_type != 'offline') {
            throw new OrderException('奖励不允许线下兑换');
        }
        $endTime = strtotime($rewardLog->created_at) + $rewardLog->expire_time * 86400;
        if ($endTime <= time() && $rewardLog->expire_time > 0) {
            throw new OrderException('奖励已过期，无法领取');
        }
        $this->rewardLog = $rewardLog;
    }

    public function getGoodsItemData($item)
    {
        $item['num'] = 1;
        return parent::getGoodsItemData($item);
    }

    public function whiteList()
    {
        return [''];
    }

    protected function getIsMinNumber()
    {
        // 线下兑换不计算起售
        return false;
    }

    /**
     * @return OrderGoodsAttr OrderGoodsAttr
     * 商品规格类
     */
    public function getGoodsAttrClass()
    {
        return new OrderGoodsAttr();
    }

    /**
     * @param Order $order
     * @param array $mchItem
     * @return bool|void
     * @throws OrderException
     */
    public function extraOrder($order, $mchItem)
    {
        $this->rewardLog->result_id = $order->id;
        if (!$this->rewardLog->save()) {
            throw new OrderException($this->getErrorMsg($this->rewardLog));
        }
        if ($order->send_type != 1) {
            $expressPrice = floatval($this->form_data['express_price_change']);
            if ($expressPrice < 0) {
                throw new OrderException('应收运费不能小于0');
            }
            if ($order->express_price != $expressPrice) {
                $order->express_price = $expressPrice;
                $order->total_pay_price = price_format($order->express_price + floatval($order->total_goods_price));
                if (!$order->save()) {
                    throw new OrderException($this->getErrorMsg($order));
                }
            }
        }
    }

    /**
     * @var User $user
     */
    private $user;

    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        }
        if (isset($this->form_data['user_id'])) {
            $this->user = User::find()->andWhere([
                'id' => $this->form_data['user_id'],
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->one();

            return $this->user;
        } else {
            throw new OrderException('请提交用户信息');
        }
    }

    protected function getAddress()
    {
        $address = new Address();
        if (isset($this->form_data['address'])) {
            $address->attributes = $this->form_data['address'];
        }
        $this->setXAddress($address);
        return $this->getXAddress();
    }

    protected function getCityAddress($mchItem)
    {
        $address = new Address();
        if (isset($this->form_data['address'])) {
            $address->attributes = $this->form_data['address'];
        }
        return $address;
    }
    
    protected function changeData($data)
    {
        return $data;
    }

    protected function checkGoodsBuyLimit($goodsList)
    {
        // 线下兑换不计算限购
        return true;
    }

    protected function getSendType($mchItem)
    {
        // 线下兑换直接全发货方式
        return ['express','offline','city'];
    }

    public function getGoodsSendType($mchItem, $sendType)
    {
        // 线下兑换不需要判断发货方式
        return $sendType;
    }

    public function getNewSendType($sendType)
    {
        // 线下兑换不需要判断发货方式
        return $sendType;
    }

    protected function getAddressEnable($address, &$mchItem)
    {
        // 线下兑换不判断区域购买
        return true;
    }

    public function checkTime($goodsList)
    {
        // 线下兑换不判断销售时间
        return true;
    }

    public function checkBuyAuth($goodsList)
    {
        // 线下兑换不判断购买权限
        return true;
    }

    public function getToken()
    {
        return $this->rewardLog->token ?: parent::getToken();
    }
}
