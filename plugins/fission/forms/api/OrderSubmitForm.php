<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/25
 * Time: 4:14 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\api;

use app\forms\api\order\OrderException;
use app\plugins\fission\models\FissionActivityReward;
use app\plugins\fission\models\FissionRewardLog;
use app\plugins\fission\Plugin;
use yii\helpers\Json;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $mallPaymentTypes = \Yii::$app->mall->getMallSettingOne('payment_type');
        $this->setEnableCoupon(false)
            ->setEnableIntegral(false)
            ->setEnableOrderForm(true)
            ->setEnableMemberPrice(false)
            ->setSupportPayTypes($mallPaymentTypes)
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
        if ($rewardLog->result_id > 0) {
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
        if ($reward->exchange_type != 'online') {
            throw new OrderException('奖励不允许线上兑换');
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

    public function extraOrder($order, $mchItem)
    {
        $this->rewardLog->result_id = $order->id;
        if (!$this->rewardLog->save()) {
            throw new OrderException($this->getErrorMsg($this->rewardLog));
        }
    }

    public function checkGoodsBuyLimit($goodsList)
    {
        return true;
    }

    public function checkBuyAuth($goodsList)
    {
        return true;
    }

    public function getToken()
    {
        return $this->rewardLog->token ?: parent::getToken();
    }
}
