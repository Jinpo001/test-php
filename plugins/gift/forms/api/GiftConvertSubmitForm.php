<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\api;

use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\models\Goods;
use app\models\Order;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSetting;
use app\plugins\gift\models\GiftUserOrder;

class GiftConvertSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    /**
     * @return $this|\app\forms\api\order\OrderSubmitForm
     */
    public function setPluginData()
    {
        $setting = GiftSetting::search();
        $this->setEnablePriceEnable(false)->setEnableAddressEnable($setting['is_territorial_limitation'] ? true : false)
            ->setSupportPayTypes($setting ? json_decode($setting['payment_type'], true) : "['online_pay']");
        return $this;
    }

    /**
     * @param $item
     * @return array
     * @throws OrderException
     */
    protected function getGoodsItemData($item)
    {
        $itemData = parent::getGoodsItemData($item);

        $itemData['total_price'] = price_format(0);//兑奖无需付款

        return $itemData;
    }

    /**
     * @param Goods $goods
     * @param $item
     * @return bool
     * @throws OrderException
     */
    protected function checkGoods($goods, $item)
    {
        parent::checkGoods($goods, $item); // TODO: Change the autogenerated stub
        //判断是否有资格兑换，goods_list中传入gift_order_id
        $user_gift_info = GiftOrder::find()->alias('go')->leftJoin(['guo' => GiftUserOrder::tableName()], 'guo.id = go.user_order_id')
            ->andWhere(['go.user_order_id' => $item['user_order_id'], 'go.order_id' => '', 'go.is_delete' => 0,
                'guo.user_id' => \Yii::$app->user->id, 'guo.is_receive' => 0, 'guo.is_delete' => 0])
            ->andWhere(['go.goods_id' => $item['id'], 'go.goods_attr_id' => $item['goods_attr_id']])
            ->select(['go.goods_id', 'go.goods_attr_id', 'go.num', 'guo.is_turn', 'guo.is_receive', 'guo.token'])->asArray()->one();
        if (empty($user_gift_info)) {
            throw new OrderException('非法兑奖，请核对是否有中奖。');
        }
        $order = Order::find()->where(['token' => $user_gift_info['token']])->andWhere(['<>', 'cancel_status', '1'])->asArray()->one();
//        var_dump($order);die;
        if (isset($order)) {
//            return [
//                'code' => 11,
//                'msg' => '地址已填写，请去待支付订单支付后领取。',
//                'order_id' => $order['id'],
//            ];
            throw new OrderException('地址已填写，请去待支付订单支付后领取。');
        }
        if ($user_gift_info['goods_id'] != $item['id'] || $user_gift_info['goods_attr_id'] != $item['goods_attr_id']
            || $user_gift_info['num'] != $item['num']) {
            throw new OrderException('非法兑奖，兑奖信息有误。');
        }
        if ($user_gift_info['is_receive'] == 1) {
            throw new OrderException('该奖品已兑换。');
        }
        if ($user_gift_info['is_turn'] == 1) {
            throw new OrderException('该奖品已转赠。');
        }
        if ($item['num'] > $user_gift_info['num']) {
            throw new OrderException('兑换数量不可超过领取数量。');
        }
        return true;
    }


    /**
     * 检查购买的商品数量是否超出限制及库存（购买数量含以往的订单）
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    protected function checkGoodsBuyLimit($goodsList)
    {
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('兑换礼物' . $goods['name'] . '数量不能小于1');
            }
        }
    }

    /**
     * 商品库存操作
     * @param OrderGoodsAttr $goodsAttr
     * @param int $subNum
     * @param array $goodsItem
     */
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        return;
    }

    /**
     * @return string
     * @throws OrderException
     */
    protected function getToken()
    {
        $user_order_info = GiftUserOrder::findOne(['id' => $this->form_data['list'][0]['goods_list'][0]['user_order_id']]);
        $user_order_info->token = parent::getToken(); // TODO: Change the autogenerated stub
        if (!$user_order_info->save()) {
            throw new OrderException($user_order_info->errors[0]);
        }
        return $user_order_info->token;
    }

    /**
     * @param $mchItem
     * @return array|\ArrayObject|mixed
     */
    protected function getSendType($mchItem)
    {
        $setting = GiftSetting::search();
        return $setting['send_type'] ? \Yii::$app->serializer->decode($setting['send_type']) : ['express'];
    }

    public function whiteList()
    {
        return [$this->sign];
    }
}
