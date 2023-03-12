<?php

namespace app\forms\common\order\send;

use CityService\Factory;
use GuzzleHttp\Client;
use app\core\response\ApiCode;
use app\forms\common\order\send\BaseSend;
use app\forms\common\order\send\city_service\CityServiceForm;
use app\forms\common\order\send\city_service\Wechat;
use app\forms\common\order\send\job\CityServiceJob;
use app\forms\common\order\send\job\DadaCityServiceJob;
use app\forms\mall\delivery\DeliveryForm;
use app\models\CityPreviewOrder;
use app\models\CityService;
use app\models\OrderDetailExpress;
use app\models\UserInfo;
use app\plugins\wxapp\models\WxappConfig;
use yii\helpers\ArrayHelper;

class OtherCitySendForm extends BaseSend
{
    public $city_service;
    public $is_preview;
    public $delivery_no;

    private $order;
    private $cityPreviewOrder;

    private $cityServiceForm;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['city_service', 'is_preview'], 'required'],
            [['city_service', 'delivery_no'], 'string'],
            [['is_preview'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'city_service' => '配送名称',
            'is_preview' => '是否预下单',
        ]);
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // 暂不支持第三方修改配送
            if ($this->express_id) {
                $orderDetailExpress = OrderDetailExpress::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $this->express_id,
                ])->one();

                if ($orderDetailExpress->send_type == 1) {
                    throw new \Exception('第三方配送暂不支持修改配送员');
                }
            }

            $order = $this->getOrder();
            $this->order = $order;
            $this->cityServiceForm = $this->getCityServiceForm();

            // 第三方配送 预下单
            if ($this->is_preview != 0) {
                $preAddOrder = $this->preAddOrder();
                $transaction->commit();

                return $preAddOrder;
            }

            // 正式下单
            $order = $this->saveOrderDetailExpress($order);
            $transaction->commit();

            //触发事件
            if ($order->is_send) {
                $this->triggerEvent($order);
            }
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发货成功',
            ];

        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine(),
            ];
        }
    }

    public function saveExtraData($orderDetailExpress)
    {
        $cityService = $this->cityServiceForm->getCityService();
        $cityInfo = [
            'city_info' => [
                'id' => $cityService->id,
                'name' => $cityService->name,
                'shop_no' => $cityService->shop_no,
            ],
            'city_service_info' => ArrayHelper::toArray($cityService),
        ];

        $orderDetailSign = $this->getOrderDetailSign();
        $this->cityPreviewOrder = CityPreviewOrder::findOne(['order_detail_sign' => $orderDetailSign]);
        if (!$this->cityPreviewOrder) {
            throw new \Exception('预下单数据不存在');
        }

        $model = $this->cityServiceForm->getModel();
        $orderInfo = $model->addOrderData($this->cityPreviewOrder);

        $allOrderInfo = json_decode($this->cityPreviewOrder->all_order_info, true);
        $shopOrderId = $allOrderInfo['shop_order_id'];
        $name = $model->getName();

        $instance = $this->cityServiceForm->getInstance();
        $result = $instance->addOrder($orderInfo);

        if (!$result->isSuccessful()) {
            throw new \Exception($result->getMessage());
        }

        $res = $model->addOrderResult($result->getOriginalData());
        // 同城速送订单号无法自定义
        if ($this->cityServiceForm->getModel()->getDivers() == 'mk') {
            $orderDetailExpress->shop_order_id = $res['data']['order_number'];
        } else {
            $orderDetailExpress->shop_order_id = $shopOrderId;
        }
        $cityInfo['result'] = $res;
        $orderDetailExpress->status = 101;
        $orderDetailExpress->city_info = json_encode($cityInfo, JSON_UNESCAPED_UNICODE);
        $orderDetailExpress->city_name = '';
        $orderDetailExpress->city_mobile = '';
        $orderDetailExpress->send_type = 1;
        $orderDetailExpress->city_service_id = $cityService->id;
        $orderDetailExpress->express_type = $name;

        $model->handleNotify($this->cityPreviewOrder);
    }

    private function getCityServiceForm()
    {
        // 从字符串中截取配送商家
        $id = substr($this->city_service, 1, strpos($this->city_service, ')') - 1);

        $cityService = CityService::find()->andWhere([
            'id' => $id,
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id
        ])->one();

        if (!$cityService) {
            throw new \Exception('所选配送商家不存在');
        }
        
        return new CityServiceForm($cityService);
    }

    // 预下单
    private function preAddOrder()
    {
        $instance = $this->cityServiceForm->getInstance();
        $model = $this->cityServiceForm->getModel();
        $allOrderInfo = $this->getOrderInfo();
        $orderInfo = $model->preOrderData($allOrderInfo);
        $result = $instance->preAddOrder($orderInfo);

        if (!$result->isSuccessful()) {
            throw new \Exception($result->getMessage());
        }

        $res = $model->preOrderResult($result->getOriginalData());

        $resultdata = [];
        $resultdata['preview_success'] = 1;
        $resultdata['name'] = $model->getName();

        $resultdata = array_merge($resultdata, $res);

        $orderDetailSign = $this->getOrderDetailSign();
        $cityPreviewOrder = CityPreviewOrder::findOne(['order_detail_sign' => $orderDetailSign]);

        if (!$cityPreviewOrder) {
            $cityPreviewOrder = new CityPreviewOrder();
        }

        $cityPreviewOrder->result_data = json_encode($resultdata, JSON_UNESCAPED_UNICODE);
        $cityPreviewOrder->order_info = json_encode($orderInfo, JSON_UNESCAPED_UNICODE);
        $cityPreviewOrder->all_order_info = json_encode($allOrderInfo, JSON_UNESCAPED_UNICODE);
        $cityPreviewOrder->order_detail_sign = $orderDetailSign;
        $previewOrderResult = $cityPreviewOrder->save();

        if (!$previewOrderResult) {
            throw new \Exception($this->getErrorMsg($previewOrderResult));
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '预下单成功',
            'data' => $resultdata,
        ];
    }

    /**
     * 注意！！！
     * 预下单调用此方法并且会存入数据库  正式下单时应去查询数据库
     * @return [type] [description]
     */
    private function getOrderInfo()
    {
        $goodsList = [];
        $goodsValue = 0;
        $goodsWeight = 0;
        $goodsCount = 0;
        $goodsName = '';
        $goodsImageUrl = '';
        $userInfo = UserInfo::findOne(['user_id' => $this->order->user_id]);
        $shopOrderId = $this->order->getOrderNo('TC'); // 用于生成唯一订单号
        foreach ($this->order->detail as $key => $value) {
            foreach ($this->order_detail_id as $detailId) {
                if ($value['id'] == $detailId) {
                    $goodsValue += $value->total_price;
                    $goodsInfo = json_decode($value->goods_info);
                    $goodsWeight += $goodsInfo->goods_attr->weight / 1000;
                    $goodsCount += $value->num;
                    $goodsName .= $goodsInfo->goods_attr->name . ' ';
                    if (!$goodsImageUrl) {
                        $goodsImageUrl = $goodsInfo->goods_attr->cover_pic;
                    }
                    $goodsList[] = [
                        'good_count' => $value->num,
                        'good_name' => $goodsInfo->goods_attr->name,
                        'good_price' => $value->unit_price,
                        'good_no' => $value->goods_no,
                        'good_unit' => $value->goods->goodsWarehouse->unit ?: '件',
                    ];
                }
            }
        }

        $address = explode(' ', $this->order->address);
        $location = explode(',', $this->order->location);

        $form = new DeliveryForm();
        $delivery = $form->getDeliveryData();

        $cityServiceData = json_decode($this->cityServiceForm->getCityService()->data, true);

        $cargoFirstClass = '';
        $cargoSecondClass = '';
        if (isset($cityServiceData['wx_product_type'])) {
            $wxProductType = json_decode($cityServiceData['wx_product_type'], true);
            $cargoFirstClass = isset($wxProductType[0]) ? $wxProductType[0] : '';
            $cargoSecondClass = isset($wxProductType[1]) ? $wxProductType[1] : '';
        }

        $data = [
            "cargo" => [
                "cargo_first_class" => $cargoFirstClass ?: "其它",
                "cargo_second_class" => $cargoSecondClass ?: "其它",
                "goods_detail" => [
                    "goods" => $goodsList,
                ],
                "goods_value" => price_format($goodsValue, 'float'),
                "goods_weight" => number_format($goodsWeight, 2), // 单位kg
            ],
            "openid" => $userInfo->platform_user_id,
            "order_info" => [
                "order_time" => time(),
                "order_type" => 0,
                "poi_seq" => $this->order->order_no,
            ],
            "receiver" => [
                "address" => isset($address[0]) ? $address[0] : '',
                "address_detail" => isset($address[1]) ? $address[1] : '',
                "city" => $this->cityServiceForm->getAddressInfo(isset($location[0]) ? $location[0] : 0, isset($location[1]) ? $location[1] : 0)['city'],
                "lat" => isset($location[1]) ? $location[1] : 0,
                "lng" => isset($location[0]) ? $location[0] : 0,
                "name" => $this->order->name,
                "phone" => $this->order->mobile,
            ],
            "sender" => [
                "address" => $delivery['address']['address'],
                "address_detail" => $delivery['address']['address'],
                "city" => $this->cityServiceForm->getAddressInfo($delivery['address']['longitude'], $delivery['address']['latitude'])['city'],
                "lat" => $delivery['address']['latitude'],
                "lng" => $delivery['address']['longitude'],
                "name" => \Yii::$app->mall->name,
                "phone" => $delivery['contact_way'],
            ],
            "shop" => [
                "goods_count" => $goodsCount,
                "goods_name" => $goodsName,
                "img_url" => $goodsImageUrl,
                "wxa_path" => "/page/order/index",
            ],
            "shop_no" => $this->cityServiceForm->getCityService()->shop_no,
            "shop_order_id" => $shopOrderId,
            'product_type' => isset($cityServiceData['product_type']) ? $cityServiceData['product_type'] : '',
            'outer_order_source_desc' => isset($cityServiceData['outer_order_source_desc']) ? $cityServiceData['outer_order_source_desc'] : '',
            'delivery_service_code' => isset($cityServiceData['delivery_service_code']) ? $cityServiceData['delivery_service_code'] : '',
        ];

        return $data;
    }

    // 该sign 为order_detail_id 想拼接 转md5
    private function getOrderDetailSign()
    {
        $string = implode(',', $this->order_detail_id);
        return md5($string);
    }
}
