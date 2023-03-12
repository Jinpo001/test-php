<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\wholesale\forms\api;

use app\plugins\wholesale\forms\common\SettingForm;
use app\plugins\wholesale\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $setting = (new SettingForm())->search();
        $this->setSign((new Plugin())->getName())
            ->setEnableFullReduce($setting['is_full_reduce'] ? true : false);
        $mallPaymentTypes = \Yii::$app->mall->getMallSettingOne('payment_type');
        $this->setSupportPayTypes($mallPaymentTypes);
        return $this;
    }

    public function checkGoods($goods, $item)
    {
        if ($goods->sign != (new Plugin())->getName()) {
            return parent::checkGoods($goods, $item);
        }

        return \Yii::$app->plugin->getPlugin($goods->sign)->checkGoods($goods, $item);
    }

    public function isGoodsEnableMemberPrice($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableMemberPrice($goodsItem);
        } else {
            return parent::isGoodsEnableMemberPrice($goodsItem);
        }
    }

    public function isGoodsEnableVipPrice($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableVipPrice($goodsItem);
        } else {
            return parent::isGoodsEnableVipPrice($goodsItem);
        }
    }

    public function isGoodsEnableIntegral($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableIntegral($goodsItem);
        } else {
            return parent::isGoodsEnableIntegral($goodsItem);
        }
    }

    public function isGoodsEnableCoupon($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableCoupon($goodsItem);
        } else {
            return parent::isGoodsEnableCoupon($goodsItem);
        }
    }

    public function isGoodsEnableAddressLimit($goodsItem)
    {
        if (isset($goodsItem['sign']) && $goodsItem['sign'] === 'flash_sale') {
            return \Yii::$app->plugin->getPlugin($goodsItem['sign'])->isGoodsEnableAddressLimit($goodsItem);
        } else {
            return parent::isGoodsEnableAddressLimit($goodsItem);
        }
    }
}
