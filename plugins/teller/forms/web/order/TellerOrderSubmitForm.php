<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web\order;

use app\core\response\ApiCode;
use app\forms\api\order\OrderException;
use app\forms\common\platform\PlatformConfig;
use app\models\Address;
use app\models\DistrictArr;
use app\models\Goods;
use app\models\Store;
use app\models\User;
use app\plugins\teller\Plugin;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerPushOrder;
use app\plugins\teller\models\TellerSales;
use app\plugins\teller\models\TellerWorkLog;

class TellerOrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    public function setPluginData()
    {
        $this->addMoney();
        if (\Yii::$app instanceof \yii\web\Application) {
            $platform = (new PlatformConfig())->getPlatform($this->getUser());
            \Yii::$app->request->headers['x-app-platform'] = $platform;
        }

        $tellerSetting = $this->getTellerSetting();

        $this->setSign((new Plugin())->getName());
        $this->setEnableOrderForm(false);
        $this->setEnableAddressEnable(false);
        $this->setEnablePriceEnable(false);
        $this->setEnableFullReduce($tellerSetting['is_full_reduce'] > 0 ? true : false);
        $this->setEnableCoupon($tellerSetting['is_coupon'] > 0 ? true : false);
        $this->setEnableVipPrice($tellerSetting['svip_status'] > 0 ? true : false);
        $this->setEnableMemberPrice($tellerSetting['is_member_price'] > 0 ? true : false);
        $this->setEnableIntegral($tellerSetting['is_integral'] > 0 ? true : false);

        return $this;
    }

    public function setSubmitData()
    {
        $tellerSetting = $this->getTellerSetting();
        
        if (!isset($this->form_data['payment_type'])) {
            throw new OrderException('请传入参数payment_type');
        }

        $paymentType = $this->form_data['payment_type'];

        if (!in_array($paymentType, $tellerSetting['payment_type'])) {
            throw new OrderException('当前支付方式未开启');
        }

        $this->setSupportPayTypes($tellerSetting['payment_type']);

        return $this;
    }

    /**
     * 添加自定义额外的订单信息
     * @param $order
     * @param $mchItem
     * @return bool
     */
    public function extraOrder($order, $mchItem)
    {
        $cashier = TellerCashier::findOne(['user_id' => $this->form_data['cashier_id']]);

        $order->store_id = $cashier->store_id;
        $order->status = 0;
        $order->is_delete = 1;
        $order->name = $this->getUser()->nickname;
        $order->back_price = isset($mchItem['back_price']) ? $mchItem['back_price'] : 0;

        // 收银台区域代理分红按门店地址
        try {
            $store = Store::findOne($cashier->store_id);
            $address = address_handle($store->address) . ' ' . $store->name;
        } catch(\Exception $exception) {
            $address = '';
        }
        $order->address = $address;
        $order->save();

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tellerOrder = new TellerOrders();
            $tellerOrder->mall_id = $order->mall_id;
            $tellerOrder->mch_id = $order->mch_id;
            $tellerOrder->order_id = $order->id;
            $tellerOrder->cashier_id = $cashier->id;
            $tellerOrder->order_type = TellerOrders::ORDER_TYPE_ORDER;

            // 导购员
            if (isset($this->form_data['sales_id']) && $this->form_data['sales_id']) {
                $tellerOrder->sales_id = $this->form_data['sales_id'];
            }

            // 加钱
            if (isset($this->form_data['add_money']) && $this->form_data['add_money']) {
                $tellerOrder->add_money = $this->form_data['add_money'];
            }

            if (isset($this->form_data['change_price_type']) && $this->form_data['change_price_type']) {
                $typeList = TellerOrders::CHANGE_PRICE_TYPE_LIST;
                $changePriceType = $this->form_data['change_price_type'];

                if (!isset($typeList[$changePriceType])) {
                    throw new OrderException(sprintf('改价类型异常%s', $changePriceType));
                }

                $tellerOrder->change_price_type = $changePriceType;
            }

            if (isset($this->form_data['change_price']) && $this->form_data['change_price']) {
                $tellerOrder->change_price = $this->form_data['change_price'];
            }


            // 关联交班记录
            $workLog = TellerWorkLog::find()->andWhere([
                'mall_id' => $order->mall_id,
                'mch_id' => $order->mch_id,
                'cashier_id' => $cashier->id,
                'is_delete' => 0,
                'status' => TellerWorkLog::PENDING
            ])->one();

            if ($workLog) {
                $tellerOrder->work_log_id = $workLog->id;
            }

            $res = $tellerOrder->save();
            if (!$res) {
                throw new OrderException($this->getErrorMsg($tellerOrder));
            }

            $transaction->commit();
        }catch(\Exception $exception) {
            $transaction->rollBack();
            throw new OrderException($exception->getMessage());
        }

        return true;
    }

    // 抹零设置
    public function getTotalPayPrice($price, $setting)
    {
        $newPrice = price_format($price);
        if ($setting['is_price']) {
            switch ($setting['price_type']) {
                // 向下抹分
                case 1:
                    $newPrice = substr($newPrice, 0, strlen($newPrice) - 1);
                    $newPrice = $newPrice . '0';
                    break;
                // 向下抹角
                case 2:
                    $newPrice = substr($newPrice, 0, strlen($newPrice) - 2);
                    $newPrice = $newPrice . '00';
                    break;
                // 四舍分
                case 3:
                    $lastNumber = substr($newPrice, -1, 1);
                    if ($lastNumber < 5) {
                        $newPrice = substr($newPrice, 0, strlen($newPrice) - 1);
                        $newPrice = $newPrice . '0';
                    }
                    break;
                // 五入到角
                case 4:
                    $lastNumber = substr($newPrice, -1, 1);
                    if ($lastNumber >= 5) {
                        $newPrice = substr($newPrice, 0, strlen($newPrice) - 1);
                        $newPrice = price_format($newPrice + 0.1);
                    }
                    break;
                default:
                    \Yii::error('抹零设置异常' . $setting['price_type']);
                    break;
            }
        }

        return $newPrice;
    }

    public function getUser()
    {
        if (isset($this->form_data['user_id']) && $this->form_data['user_id']) {
            $user = User::find()->andWhere([
                'id' => $this->form_data['user_id'],
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->one();

            return $user;
        }

    	$setting = $this->getTellerSetting();

    	return User::findOne($setting['user_id']);
    }

    private function getTellerSetting()
    {
        $tellerSetting = (new CommonTellerSetting())->search();

        return $tellerSetting;
    }

    /**
     * 收银台 
     */
    protected function getSendType($mchItem)
    {
        return ['none'];
    }

    protected function changeData($data)
    {
        return $data;
    }

    protected function setExpressData($mchItem)
    {
        return $mchItem;
    }

    /**
     * 获取用户的收货地址(仅快递或自提类地址)
     * @return null|Address
     */
    protected function getAddress()
    {
        if ($this->xAddress) {
            return $this->xAddress;
        }

        $user = $this->getUser();
        if (!$this->form_data['address_id']) {
            $this->xAddress = Address::findOne([
                'user_id' => $user->id,
                'is_delete' => 0,
                'is_default' => 1,
                'type' => 0,
            ]);
        } else {
            $this->xAddress = Address::findOne([
                'user_id' => $user->id,
                'is_delete' => 0,
                'id' => $this->form_data['address_id'],
                'type' => 0,
            ]);
        }

        // 当没有用户地址时  设置默认收货地址
        if (!$this->xAddress) {
            $address = new Address();
            $address->name = $user->nickname;
            $address->mobile = $user->mobile;

            $this->xAddress = $address;
        }

        return $this->xAddress;
    }

    // 当前订单可用优惠券列表
    public function getCoupon()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $this->getUsableCouponList($this->form_data)
            ]
        ];
    }

    // 订单加钱
    private function addMoney()
    {
        $tellerSetting = $this->getTellerSetting();

        if (isset($this->form_data['add_money']) && $this->form_data['add_money']) {

            if (!$tellerSetting['is_add_money']) {
                throw new OrderException('加价开关未开启');
            }

            $goods = Goods::find()->andWhere([
                'id' => $tellerSetting['goods_id'],
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
            ])->with('attr')->one();

            if (!$goods) {
                throw new OrderException('加价商品不存在');
            }
        }
    }

    public function whiteList()
    {
        $name = (new Plugin())->getName();
        return array_merge(parent::whiteList(), [$name]);
    }

    // 改价是在所有优惠之后
    public function checkChangePrice($mchItem)
    {
        $tellerSetting = $this->getTellerSetting();
        $mchItem['change_price_data'] = [
            'most_plus_start' => 0.01,
            'most_subtract_start' => 0.01
        ];

        // 商品总价
        $totalPrice = 0;
        foreach ($mchItem['goods_list'] as &$item) {
            $totalPrice += $item['total_price'];
            $item['back_price'] = 0;
            $item['change_price'] = 0;
        }

        if (!isset($this->form_data['change_price'])) {
            $this->form_data['change_price'] = 0;
        }
        // 固定金额
        if ($tellerSetting['is_goods_change_price_type'] == 1) {
            $mchItem['change_price_data']['most_plus_end'] = $tellerSetting['most_plus'];
            $mchItem['change_price_data']['most_subtract_end'] = $tellerSetting['most_subtract'];

            // 最多可加
            if ($this->form_data['change_price_type'] == TellerOrders::CHANGE_PRICE_TYPE_ADD && $this->form_data['change_price'] > $tellerSetting['most_plus']) {
                $this->form_data['change_price'] = $tellerSetting['most_plus'];
            }
            // 最多可减
            if ($this->form_data['change_price_type'] == TellerOrders::CHANGE_PRICE_TYPE_SUBTRACT) {
                if ($this->form_data['change_price'] > $tellerSetting['most_subtract']) {
                    $this->form_data['change_price'] = $tellerSetting['most_subtract'];
                }

                if ($this->form_data['change_price'] > $totalPrice) {
                    $this->form_data['change_price'] = $totalPrice;
                    // 最多可减不能大于订单价格
                    $mchItem['change_price_data']['most_subtract_end'] = $totalPrice;
                }
            }
        }

        // 百分比
        if ($tellerSetting['is_goods_change_price_type'] == 2) {
            // 最多可加
            $plusPrice = price_format($totalPrice * ($tellerSetting['most_plus_percent'] / 100));
            if ($this->form_data['change_price_type'] == TellerOrders::CHANGE_PRICE_TYPE_ADD && $this->form_data['change_price'] > $plusPrice) {
                $this->form_data['change_price'] = $plusPrice;
            }

            $subtractPrice = price_format($totalPrice * ($tellerSetting['most_subtract_percent'] / 100));
            if ($this->form_data['change_price_type'] == TellerOrders::CHANGE_PRICE_TYPE_SUBTRACT && $this->form_data['change_price'] > $subtractPrice) {
                $this->form_data['change_price'] = $subtractPrice - $this->form_data['change_price'];
            }

            $mchItem['change_price_data']['most_plus_end'] = $plusPrice;
            $mchItem['change_price_data']['most_subtract_end'] = $subtractPrice;
        }

        if (isset($this->form_data['change_price']) && $this->form_data['change_price'] > 0 && isset($this->form_data['change_price_type'])) {

            if (!isset(TellerOrders::CHANGE_PRICE_TYPE_LIST[$this->form_data['change_price_type']])) {
                throw new OrderException(sprintf('改价类型异常%s', $this->form_data['change_price_type']));
            }

            if (!$tellerSetting['is_goods_change_price']) {
                throw new OrderException('改价开关未开启');
            }

            $surplusPrice = $this->form_data['change_price'];
            $index = count($mchItem['goods_list']) - 1;
            // TODO 应该先升序排序
            foreach ($mchItem['goods_list'] as $key => &$item) {
                // totalPrice 需大于0才进行改价
                if ($totalPrice <= 0) {
                    continue;
                }

                // 按比例分配改价金额
                $price = price_format(($item['total_price'] / $totalPrice) * $this->form_data['change_price']);
                // 加价
                if ($this->form_data['change_price_type'] == TellerOrders::CHANGE_PRICE_TYPE_ADD) {
                    // 剩余的钱 加入最后一个商品价格上
                    if ($index == $key) {
                        $item['total_price'] = $item['total_price'] + $surplusPrice;
                        $item['change_price'] = $surplusPrice;  
                    } else {
                        $item['total_price'] = $item['total_price'] + $price;
                        $item['change_price'] = $price;
                        $surplusPrice = price_format($surplusPrice - $price);
                    }
                    $item['back_price'] = -$item['change_price'];
                }

                // 减价
                if ($this->form_data['change_price_type'] == TellerOrders::CHANGE_PRICE_TYPE_SUBTRACT) {
                    if ($index == $key) {
                        $item['total_price'] = $item['total_price'] - $surplusPrice;
                        $item['change_price'] = $surplusPrice;
                    } else {
                        $item['total_price'] = $item['total_price'] - $price;
                        $item['change_price'] = $price;
                        $surplusPrice = price_format($surplusPrice - $price);
                    }
                    $item['back_price'] = $item['change_price'];
                }
            }
        }

        return $mchItem;
    }

    // 加价商品价格
    protected function getGoodsItemData($item)
    {
        $tellerSetting = $this->getTellerSetting();
        if ($tellerSetting['goods_id'] == $item['id']) {
            $data = parent::getGoodsItemData($item);
            $data['unit_price'] = price_format($this->form_data['add_money']);
            $data['total_original_price'] = price_format($this->form_data['add_money'] * $item['num']);
            $data['total_price'] = price_format($this->form_data['add_money'] * $item['num']);
        } else {
            $data = parent::getGoodsItemData($item);
        }

        return $data;
    }

    /**
     * 获取1个或多个订单的数据，按商户划分
     * @return array ['mch_list'=>'商户列表', 'total_price' => '多个订单的总金额（含运费）']
     * @throws OrderException
     * @throws \yii\db\Exception
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    public function getAllData()
    {
        $data = parent::getAllData();
        $data['price_enable'] = true; // 起送规则不验证
        $data['time'] = isset($this->form_data['time']) ? $this->form_data['time'] : 0;
        $tellerSetting = $this->getTellerSetting();

        $data['total_price'] = 0;
        foreach ($data['mch_list'] as &$item) {
            if (empty($item['goods_list']) || count($item['goods_list']) == 0) {
                throw new OrderException('请选择商品');
            }
            $item = $this->checkChangePrice($item);

            $totalChangePrice = 0;
            $totalPrice = 0;
            $backPrice = 0;
            foreach ($item['goods_list'] as &$goods) {
                if (isset($this->form_data['change_price_type']) && $this->form_data['change_price_type'] && isset($this->form_data['change_price']) && $this->form_data['change_price'] > 0) {
                    $totalChangePrice += $goods['change_price'];
                }
                $totalPrice += $goods['total_price'];
                $backPrice += $goods['back_price'];
            }

            $item['total_change_price'] = price_format($totalChangePrice);
            $item['change_price_type'] = $this->form_data['change_price_type'];
            $item['total_price'] = price_format($totalPrice);
            $item['back_price'] = price_format($backPrice);

            $data['total_price'] = price_format($data['total_price'] + $item['total_price']);

            // 计算改价优惠 $backPrice 正数为减价 负数为加价
            $item['total_discounts_price'] = price_format($item['total_discounts_price'] + $backPrice);
        }

        // 抹零设置
        if ($tellerSetting['is_price'] == 1) {
            $data['total_price'] = 0; // 由于抹零 $data['total_price'] 需要重新计算
            $data['erase_price'] = 0;// 收银台订单只会有一个mch_list 所以现在是按mch_list抹零
            foreach ($data['mch_list'] as &$item) {
                if ($item['total_price'] <= 0) {
                    continue;
                }
                $newTotalPayPrice = $this->getTotalPayPrice($item['total_price'], $tellerSetting);
                // 抹零的价格 有正数 或 负数的情况
                $erasePrice = price_format($newTotalPayPrice - $item['total_price']);

                $item['erase_price'] = $erasePrice;

                if ($item['total_price'] > 0) {
                    $surplusPrice = $erasePrice;
                    $index = count($item['goods_list']) - 1;
                    // TODO goods_list 应该按价格升序排序保证最后一个是有价格的 要不然计算会有问题
                    foreach ($item['goods_list'] as $key => &$goods) {
                        // 按比例分配改价金额
                        $price = price_format(($goods['total_price'] / $item['total_price']) * $erasePrice);
                        $goods['erase_price'] = $price;
                        if ($index == $key) {
                            $goods['total_price'] = price_format($goods['total_price'] + $surplusPrice);
                        } else {
                            $goods['total_price'] = price_format($goods['total_price'] + $price);
                            // $erasePrice 有正数 或 负数的情况
                            $surplusPrice = $erasePrice > 0 ? $surplusPrice - $price : $surplusPrice + $price;
                            $surplusPrice = price_format($surplusPrice);
                        }
                    }
                }
                // 赋值要在前面的计算之后
                $item['total_price'] = price_format($item['total_price'] + $erasePrice);
                $data['total_price'] = price_format($data['total_price'] + $item['total_price']);
                $data['erase_price'] = price_format($data['erase_price'] + $item['erase_price']);

                // 计算抹零优惠 正数为加价 负数为减价
                $erasePrice = $erasePrice >= 0 ? ('-' . $erasePrice) : abs($erasePrice);
                $item['total_discounts_price'] = price_format($item['total_discounts_price'] + $erasePrice);
            }
        }

        return $data;
    }

    public function extraGoodsDetail($order, $goodsItem)
    {
        // 分销开关是通过订单详情的sign 来查询插件Plugin
        $goodsItem['sign'] = (new Plugin())->getName();

        return parent::extraGoodsDetail($order, $goodsItem);
    }

    protected function getTemplateMessage()
    {
        return [];
    }

    protected function setMemberDiscountData($mchItem)
    {
        $tellerSetting = (new CommonTellerSetting())->search();
        // 购买商品可以升级会员 收银台匿名用户也可能是会员
        // 如果是匿名用户 则不进行会员计算
        if ($tellerSetting['user_id'] == $this->getUser()->id) {
            $mchItem['member_discount'] = price_format(0);
            return $mchItem;
        }

        return parent::setMemberDiscountData($mchItem);
    }

    protected function checkGoodsBuyLimit($goodsList)
    {
        $tellerSetting = (new CommonTellerSetting())->search();
        // 如果是匿名用户 则不进行限购
        if ($tellerSetting['user_id'] == $this->getUser()->id) {
            return true;
        }

        return parent::checkGoodsBuyLimit($goodsList);
    }

    protected function setStoreData($mchItem, $formMchItem, $formData)
    {
        if (isset($formData['cashier_id']) && $formData['cashier_id']) {
            $cashier = TellerCashier::findOne(['user_id' => $formData['cashier_id']]);
            $mchItem['store'] = $cashier->store;

            return $mchItem;
        } else {
            return parent::setStoreData($mchItem, $formMchItem, $formData);
        }
    }

    /**
     * @param $mchItem
     * @param array $sendType 默认发货方式
     * @return array|false|string[]
     * @throws OrderException
     * 获取单独商品设置的发货方式
     */
    public function getGoodsSendType($mchItem, $sendType)
    {
        return ['none'];
    }
}
