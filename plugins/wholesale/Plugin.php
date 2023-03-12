<?php


namespace app\plugins\wholesale;


use app\forms\api\order\OrderException;
use app\forms\api\order\OrderGoodsAttr;
use app\forms\common\goods\GoodsAuth;
use app\forms\PickLinkForm;
use app\forms\OrderConfig;
use app\handlers\HandlerBase;
use app\helpers\PluginHelper;
use app\models\UserIdentity;
use app\plugins\wholesale\forms\api\CartForm;
use app\plugins\wholesale\handlers\HandlerRegister;
use app\plugins\wholesale\forms\common\SettingForm;
use app\plugins\wholesale\forms\api\GoodsForm;
use app\plugins\wholesale\forms\mall\StatisticsForm;
use app\plugins\wholesale\models\WholesaleGoods;
use app\plugins\wholesale\models\WholesaleOrder;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '批发设置',
                'route' => 'plugin/wholesale/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '商品管理',
                'route' => 'plugin/wholesale/mall/goods/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '商品编辑',
                        'route' => 'plugin/wholesale/mall/goods/edit',
                    ],
                ],
            ],
            $this->getStatisticsMenus(false)
        ];
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $HandlerClasses = $register->getHandlers();
        foreach ($HandlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                /** @var HandlerBase $handler */
                $handler->register();
            }
        }
        return $this;
    }

    //商品详情路径
    public function getGoodsUrl($item)
    {
        return sprintf("/plugins/wholesale/goods/goods?id=%u", $item['id']);
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'wholesale';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '商品批发';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg'
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/wholesale/mall/setting/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'wholesale',
                'name' => '商品批发',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-wholesale.png',
                'value' => '/plugins/wholesale/index/index',
                'ignore' => [],
            ],
            [
                'key' => 'wholesale',
                'name' => '批发商品详情',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-wholesale.png',
                'value' => '/plugins/wholesale/goods/goods',
                'params' => [
                    [
                        'key' => 'id',
                        'value' => '',
                        'desc' => '请填写批发商品ID',
                        'is_required' => true,
                        'data_type' => 'number',
                        'page_url' => '/plugin/wholesale/mall/goods/index',
                        'pic_url' => $iconBaseUrl . '/example_image/goods-id.png',
                        'page_url_text' => '商品列表',
                    ],
                ],
                'ignore' => [PickLinkForm::IGNORE_NAVIGATE],
            ],
        ];
    }

    /**
     * 返回实例化后台统计数据接口
     * @return StatisticsForm
     */
    public function getApi()
    {
        return new StatisticsForm();
    }

    public function getStatisticsMenus($bool = true)
    {
        return [
            'is_statistics_show' => $bool,
            'name' => $bool ? $this->getDisplayName() : '插件统计',
            'pic_url' => $this->getStatisticIconUrl(),
            'key' => $this->getName(),
            'pic_url' => $this->getStatisticIconUrl(),
            'route' => 'mall/wholesale-statistics/index',
        ];
    }

    public function getHomePage($type)
    {
        if ($type == 'mall') {
            $baseUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            return [
                'list' => [
                    [
                        'key' => $this->getName(),
                        'name' => $this->getDisplayName(),
                        'relation_id' => 0,
                        'is_edit' => 1,
                    ],
                ],
                'bgUrl' => [
                    $this->getName() => [
                        'bg_url' => $baseUrl . '/statics/img/mall/home_block/yushou-bg.png',
                    ],
                ],
                'key' => $this->getName(),
            ];
        } elseif ($type == 'api') {
            $form = new GoodsForm();
            $form->attributes = \Yii::$app->request->get();

            return ($form->getList())['data'];
        }
    }

    public function getOrderConfig()
    {
        $setting = (new SettingForm())->search();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
            'is_member_price' => $setting['is_member_price'],
        ]);

        return $config;
    }

    public function getOrderInfo($orderId, $order)
    {
        $wholesaleOrder = WholesaleOrder::findOne(['order_id' => $orderId]);
        if ($wholesaleOrder) {
            $data = [
                'discount_list' => [
                    'wholesale_discount' => [
                        'label' => '批发优惠',
                        'value' => $wholesaleOrder->discount,
                    ],
                ],
                'print_list' => [
                    'wholesale_discount' => [
                        'label' => '批发优惠',
                        'value' => $wholesaleOrder->discount,
                    ],
                ],
            ];
            return $data;
        }
    }

    private $pluginSetting;

    public function getPluginSetting()
    {
        if ($this->pluginSetting !== null) {
            return $this->pluginSetting;
        }
        $this->pluginSetting = (new SettingForm())->search();
        return $this->pluginSetting;
    }

    public function getGoodsData($array)
    {
        $form = new GoodsForm();
        $form->attributes = $array;
        return $form->getList()['data'];
    }

    public function isGoodsEnableMemberPrice($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_member_price'] ? true : false;
    }

    public function isGoodsEnableVipPrice($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['svip_status'] ? true : false;
    }

    public function isGoodsEnableIntegral($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_integral'] ? true : false;
    }

    public function isGoodsEnableCoupon($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_coupon'] ? true : false;
    }

    public function isGoodsEnableAddressLimit($goodsItem)
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['is_territorial_limitation'] ? true : false;
    }

    public function getEnableVipDiscount()
    {
        $pluginSetting = $this->getPluginSetting();
        return $pluginSetting['svip_status'] == 0 ? false : true;
    }

    public function checkGoods($goods, $item)
    {
//        $wholesale_goods_info = WholesaleGoods::findOne(['goods_id' => $goods->id, 'is_delete' => 0]);
//        if (empty($wholesale_goods_info)) {
//            throw new OrderException('批发商品信息有误');
//        }
//        if ($wholesale_goods_info->rise_num > $item['num']) {
//            throw new OrderException('商品【' . $goods->name . '】未达到起批数');
//        }
    }

    /**
     * 商品优惠
     * @param $goodsItem
     * @param $mchItem
     * @param $goods_num_info
     * @return mixed
     */
    public function pluginDiscount($goodsItem, $mchItem, $goods_num_info)
    {
        $wholesale_goods_info = WholesaleGoods::findOne(['goods_id' => $goodsItem['id'], 'is_delete' => 0]);
        $goodsItem['wholesale_discount'] = price_format(0);
        if (!isset($mchItem['wholesale_discount'])) {
            $mchItem['wholesale_discount'] = price_format(0);
        }
        //规则未开启
        if ($wholesale_goods_info->rules_status != 1) {
            return [$goodsItem, $mchItem];
        }

        if (empty($wholesale_goods_info)) {
            throw new OrderException('批发商品信息有误');
        }
        if (isset($goods_num_info) && $wholesale_goods_info->rise_num > $goods_num_info[$goodsItem['id']] ?? 0) {
            throw new OrderException('商品【' . $goodsItem['name'] . '】未达到起批数');
        }

        $wholesaleUnitPrice = null;
        /* @var OrderGoodsAttr $goodsAttr */
        $goodsAttr = $goodsItem['goods_attr'];

        //批发优惠计算
        $rules = json_decode($wholesale_goods_info->wholesale_rules, true);
        $discount = null;
        foreach ($rules as $rule) {
            if (isset($goods_num_info) && $goods_num_info[$goodsItem['id']] >= $rule['num']) {
                $discount = $rule['discount'];
            }
        }
        if ($discount > 0) {
            switch ($wholesale_goods_info->type) {
                case 0:
                    $wholesaleUnitPrice = $goodsItem['total_price'] * $discount / 10;
                    break;
                case 1:
                    $wholesaleUnitPrice = $goodsItem['total_price'] - min($goodsItem['total_price'], ($discount * $goodsItem['num']));
                    break;
            }
        }

        if (is_numeric($wholesaleUnitPrice) && $wholesaleUnitPrice >= 0) {
            // 商品单件价格（批发优惠后）
            $goodsAttr->price = $wholesaleUnitPrice;
            $wholesaleSubPrice = $goodsItem['total_price'] - $wholesaleUnitPrice;
            if ($wholesaleSubPrice != 0) {
                $wholesaleSubPrice = min($goodsItem['total_price'], $wholesaleSubPrice);
                $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $wholesaleSubPrice);
                $mchItem['wholesale_discount'] += $wholesaleSubPrice;
                $goodsItem['discounts'][] = [
                    'name' => '批发优惠',
                    'value' => $wholesaleSubPrice >= 0 ?
                        ('-' . price_format($wholesaleSubPrice))
                        : ('+' . price_format(0 - $wholesaleSubPrice))
                ];
                $mchItem['total_goods_price'] = price_format($mchItem['total_goods_price'] - $wholesaleSubPrice);
                $goodsItem['wholesale_discount'] = price_format($wholesaleSubPrice);
            }
        }
        return [$goodsItem, $mchItem];
    }


    /**
     * 订单优惠信息
     * @param $mchItem
     * @return mixed
     */
    public function pluginDiscountData($mchItem)
    {
        if ($mchItem['wholesale_discount'] > 0) {
            $mchItem['discounts'][] = [
                'title' => '批发优惠',
                'value' => $mchItem['wholesale_discount'] >= 0 ?
                    ('-' . price_format($mchItem['wholesale_discount']))
                    : ('+' . price_format(0 - $mchItem['wholesale_discount']))
            ];
        }
        return $mchItem;
    }


    public function getCartList()
    {
        $form = new CartForm();
        $res = $form->getCartList();
        return $res;
    }

    public function getGoodsExtra($goods)
    {
        if ($goods->sign != $this->getName()) {
            return [];
        }
        return [
            'wholesaleGoods' => $goods->wholesaleGoods ?? '',
            'goodsWarehouse' => $goods->goodsWarehouse,
            'is_negotiable' => $this->getIsNegotiable($goods),
            'wholesaleGoods' => $goods->wholesaleGoods ?? '',
            'goodsWarehouse' => $goods->goodsWarehouse,
        ];
    }

    public function getEnableFullReduce()
    {
        $setting = (new SettingForm())->search();
        return $setting['is_full_reduce'] == 0 ? false : true;
    }

    public function getIsNegotiable($goods)
    {
        $setting = $this->getPluginSetting();
        $is_negotiable = 1;
        if ($setting['is_vip_show']) {
            $vip_arr = $setting['vip_show_limit'];
            if (!empty($vip_arr)) {
                $userIdentity = \Yii::$app->user->identity->identity;
                if ($userIdentity && in_array($userIdentity->member_level, $vip_arr)) {
                    $is_negotiable = 0;
                }
            } else {
                $is_negotiable = 0;
            }
        } else {
            $is_negotiable = 0;
        }
        return $is_negotiable;
    }

    public function goodsAuth()
    {
        $config = parent::goodsAuth();
        $config['is_show_and_buy_auth'] = false;
        return $config;
    }
}
