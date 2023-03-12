<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/18
 * Time: 1:52 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\forms\common\platform\PlatformConfig;
use app\models\PaymentOrderUnion;
use app\plugins\minishop\models\MinishopOrder;
use yii\helpers\Html;
use yii\helpers\Json;

class PaymentForm extends Model
{
    /**
     * @var PaymentOrderUnion $paymentOrderUnion
     */
    public $paymentOrderUnion;

    public $payData;

    public function add()
    {
        \Yii::warning('---自定义版交易组件下单---');
        try {
            if (!in_array('minishop', \Yii::$app->mall->role->permission)) {
                \Yii::warning('没权限');
                return [];
            }
            if ($this->paymentOrderUnion->platform !== 'wxapp') {
                \Yii::warning('非微信小程序订单不上传到小商店');
                return [];
            }
            $platformList = PlatformConfig::getInstance()->getPlatformOpenid($this->paymentOrderUnion->user);

            if (!isset($platformList['wxapp'])) {
                \Yii::warning('获取不到微信小程序用户openid不上传到小商店');
                return [];
            }
            $openid = $platformList['wxapp'];
            $form = new CheckForm();
            $plugin = $form->check();
            $shopService = $plugin->getShopService();
            $res = $shopService->register->check();
            if (!in_array($res['data']['status'], [1, 2])) {
                return [];
            }
            $orderParams = [
                'path' => '/pages/order/index/index',
            ];
            $productInfos = [];
            $freight = 0;
            $orderPrice = 0;
            $deliveryType = 1;
            $addressInfo = [];
            foreach ($this->paymentOrderUnion->paymentOrder as $paymentOrder) {
                if ($paymentOrder->order->sign === '') {
                    switch ($paymentOrder->order->send_type) {
                        case 1:
                            $deliveryType = 4;
                            $address = $paymentOrder->order->store->address;
                            break;
                        case 2:
                            $deliveryType = 3;
                            $address = $paymentOrder->order->address;
                            break;
                        default:
                            $deliveryType = 1;
                            $address = $paymentOrder->order->address;
                    }
                    $addressInfo = [
                        'receiver_name' => $paymentOrder->order->name,
                        'detailed_address' => $address,
                        'tel_number' => $paymentOrder->order->mobile
                    ];
                }
                $orderPrice += floatval($paymentOrder->order->total_goods_price);
                $freight += floatval($paymentOrder->order->express_price);
                foreach ($paymentOrder->order->detail as $detail) {
                    $goodsInfo = Json::decode($detail->goods_info, true);
                    $goodsParams = [
                        'path' => '/pages/goods/goods',
                        'id' => $detail->id
                    ];
                    $productInfos[] = [
                        'out_detail_id' => $detail->id,
                        'out_product_id' => $detail->goods_id,
                        'out_sku_id' => $goodsInfo['goods_attr']['id'],
                        'product_cnt' => $detail->num,
                        'sale_price' => $detail->total_price * 100,
                        'head_img' => $goodsInfo['goods_attr']['pic_url'] ?: $goodsInfo['goods_attr']['cover_pic'],
                        'title' => $goodsInfo['goods_attr']['name'],
                        'path' => Html::encode('/pages/index/index?scene=share&params=') . Json::encode($goodsParams, JSON_UNESCAPED_UNICODE)
                    ];
                }
            }
            $args = [
                'create_time' => $this->paymentOrderUnion->created_at,
                'type' => 0,
                'out_order_id' => $this->paymentOrderUnion->id,
                'openid' => $openid,
                'path' => Html::encode('/pages/index/index?scene=share&params=') . Json::encode($orderParams, JSON_UNESCAPED_UNICODE),
                'out_user_id' => $this->paymentOrderUnion->user_id,
                'order_detail' => [
                    'product_infos' => $productInfos,
                    'pay_info' => [
                        'pay_method' => '微信支付',
                        'prepay_id' => $this->payData['prepay_id'],
                        'prepay_time' => mysql_timestamp(),
                    ],
                    'price_info' => [
                        'order_price' => $orderPrice * 100,
                        'freight' => $freight * 100,
                        'discounted_price' => 0,
                        'additional_price' => 0,
                        'additional_remarks' => '',
                    ]
                ],
                'delivery_detail' => [
                    'delivery_type' => $deliveryType
                ],
                'address_info' => [
                    'receiver_name' => $addressInfo['receiver_name'],
                    'detailed_address' => $addressInfo['detailed_address'],
                    'tel_number' => $addressInfo['tel_number'],
                    'country' => '',
                    'province' => '',
                    'city' => '',
                    'town' => '',
                ]
            ];
            $res = $shopService->order->add($args);
            \Yii::warning($res);
            $model = MinishopOrder::findOne([
                'payment_order_union_id' => $this->paymentOrderUnion->id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            if (!$model) {
                $model = new MinishopOrder();
                $model->payment_order_union_id = $this->paymentOrderUnion->id;
                $model->mall_id = \Yii::$app->mall->id;
            }
            $model->ticket = $res['data']['ticket'];
            $model->ticket_expire_time = $res['data']['ticket_expire_time'];
            $model->order_id = $res['data']['order_id'] . '';
            $model->final_price = $res['data']['final_price'];
            $model->status = 10;
            $model->data = Json::encode($args, JSON_UNESCAPED_UNICODE);
            if (!$model->save()) {
                \Yii::warning($this->getErrorMsg($model));
            }
            return $args;
        } catch (\Exception $exception) {
            \Yii::warning($exception);
        }
        return [];
    }
}
