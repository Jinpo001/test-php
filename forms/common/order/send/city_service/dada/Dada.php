<?php

namespace app\forms\common\order\send\city_service\dada;

use app\forms\common\order\send\city_service\BaseCityService;
use app\forms\common\order\send\job\DadaCityServiceJob;

class Dada extends BaseCityService
{
    public function getDivers()
    {
        return 'dada';
    }

    public function getName()
    {
        return '达达';
    }

    public function getConfig()
    {
        $cityService = $this->cityServiceForm->getCityService();
        $data = json_decode($cityService->data, true);
        return [
            'appKey' => $data['appkey'],
            'appSecret' => $data['appsecret'],
            'sourceId' => $data['shop_id'],
            'debug' => $data['is_debug'] ? true : false,
        ];
    }

    public function preOrderResult(array $result)
    {
    	$result = $result['result'];
        $result['fee'] = number_format($result['fee'], 2);

    	return $result;
    }
    
    public function addOrderResult(array $result)
    {
    	return $result;
    }

    public function preOrderData($orderInfo): array
    {
        $instance = $this->cityServiceForm->getInstance();

        $cityName = $this->cityServiceForm->getAddressInfo($orderInfo['receiver']['lng'], $orderInfo['receiver']['lat'])['new_city'];
        $response = $instance->getCityCodeList();
        $cityCode = '';

        if (!$response->isSuccessful()) {
            throw new \Exception($response->getMessage());
        }

        $resultData = $response->getOriginalData();

        foreach ($resultData['result'] as $key => $item) {
            if ($item['cityName'] == $cityName) {
                $cityCode = $item['cityCode'];
            }
        }

        if (!$cityCode) {
            throw new \Exception($cityName . '不支持配送');
        }

        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/msg-notify/dada-city-service.php';

        $productList = [];
        foreach ($orderInfo['cargo']['goods_detail']['goods'] as $key => $item) {
            $productList[] = [
                'sku_name' => $item['good_name'],
                'src_product_no' => $item['good_no'] ?: '',
                'count' => number_format($item['good_count'], 2),
            ];
        }

        return [
            'shop_no' => $orderInfo['shop_no'],
            'origin_id' => $orderInfo['shop_order_id'],
            'city_code' => $cityCode,
            'cargo_price' => $orderInfo['cargo']['goods_value'],
            'is_prepay' => 0,
            'receiver_name' => $orderInfo['receiver']['name'],
            'receiver_phone' => $orderInfo['receiver']['phone'],
            'receiver_address' => $orderInfo['receiver']['address'] . $orderInfo['receiver']['address_detail'],
            'callback' => $url,
            'cargo_weight' => $orderInfo['cargo']['goods_weight'] > 0 ? doubleval($orderInfo['cargo']['goods_weight']) : 1,
            'product_list' => $productList,
        ];
    }

    public function addOrderData($cityPreviewOrder): array
    {
        $resultData = json_decode($cityPreviewOrder->result_data, true);
        $orderInfo = json_decode($cityPreviewOrder->order_info, true);
        return [
            'deliveryNo' => $resultData['deliveryNo'],
            'shop_order_id' => $orderInfo['origin_id'],
        ];
    }

    public function handleNotify($cityPreviewOrder)
    {
        $allOrderInfo = json_decode($cityPreviewOrder->all_order_info, true);
        $instance = $this->cityServiceForm->getInstance();
        $enableDebug = $this->cityServiceForm->getEnabledDebug();
        $shopOrderId = $allOrderInfo['shop_order_id'];

        if ($enableDebug) {
            // 分配骑手
            \Yii::$app->queue->delay(10)->push(new DadaCityServiceJob([
                'shopOrderId' => $shopOrderId,
                'mock_type' => 'accept',
                'instance' => $instance,
            ]));
            // 骑手取货
            \Yii::$app->queue->delay(20)->push(new DadaCityServiceJob([
                'shopOrderId' => $shopOrderId,
                'mock_type' => 'fetch',
                'instance' => $instance,
            ]));
            // 配送完成
            \Yii::$app->queue->delay(30)->push(new DadaCityServiceJob([
                'shopOrderId' => $shopOrderId,
                'mock_type' => 'finish',
                'instance' => $instance,
            ]));
        }
    }

    public function handleNotifyData(array $data)
    {

    }
}
