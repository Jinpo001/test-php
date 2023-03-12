<?php

namespace app\forms\common\order\send\city_service\mt;

use app\forms\common\order\send\city_service\BaseCityService;
use app\forms\common\order\send\job\MtCityServiceJob;


class Mt extends BaseCityService
{
    public function getDivers()
    {
        return 'mt';
    }

    public function getName()
    {
        return '美团配送';
    }

    public function getConfig()
    {
        $cityService = $this->cityServiceForm->getCityService();
        $data = json_decode($cityService->data, true);
        return [
            'appKey' => $data['appkey'],
            'appSecret' => $data['appsecret'],
            'shopId' => $cityService->shop_no,
        ];
    }

    public function preOrderResult(array $result)
    {
    	$result = $result['data'];
        $result['fee'] = $result['delivery_fee'];

    	return $result;
    }
    
    public function addOrderResult(array $result)
    {
    	return $result;
    }

    public function preOrderData($data): array
    {
        if (isset($data['outer_order_source_desc'])) {
            if (is_numeric($data['outer_order_source_desc'])) {
                $data['outer_order_source_desc'] = $data['outer_order_source_desc'] ? (int)$data['outer_order_source_desc'] : '其它';
            } else {
                $data['outer_order_source_desc'] = $data['outer_order_source_desc'] ?: '其它';
            }
        }

        $data['delivery_service_code'] = isset($data['delivery_service_code']) ? (int)$data['delivery_service_code'] : 4002;
        $data['delivery_id'] = time();

        $data['receiver']['lat'] = substr($data['receiver']['lat'], 0, 9) * pow(10, 6);
        $data['receiver']['lng'] = substr($data['receiver']['lng'], 0, 9) * pow(10, 6);
        $data['sender']['lat'] = substr($data['sender']['lat'], 0, 9) * pow(10, 6);
        $data['sender']['lng'] = substr($data['sender']['lng'], 0, 9) * pow(10, 6);

        $goodsDetail = [];
        foreach ($data['cargo']['goods_detail']['goods'] as $key => $item) {
            $goodsDetail['goods'][] = [
                'goodName' => $item['good_name'],
                'goodPrice' => $item['good_price'],
                'goodCount' => (int) $item['good_count'],
                'goodUnit' => $item['good_unit'],
            ];
        }

        return [
            'delivery_id' => $data['delivery_id'],
            'order_id' => $data['shop_order_id'],
            'outer_order_source_desc' => $data['outer_order_source_desc'],
            'delivery_service_code' => $data['delivery_service_code'],
            'receiver_name' => $data['receiver']['name'],
            'receiver_address' => $data['receiver']['address'] . $data['receiver']['address_detail'],
            'receiver_phone' => $data['receiver']['phone'],
            'receiver_lng' => $data['receiver']['lng'],
            'receiver_lat' => $data['receiver']['lat'],
            'goods_value' => $data['cargo']['goods_value'],
            'goods_weight' => $data['cargo']['goods_weight'] > 0 ? doubleval($data['cargo']['goods_weight']) : 1,
            'goods_detail' => json_encode($goodsDetail, JSON_UNESCAPED_UNICODE),
            'poi_seq' => rand(1,9999),
        ];
    }

    public function addOrderData($cityPreviewOrder): array
    {
        $allOrderInfo = json_decode($cityPreviewOrder->all_order_info, true);
        $orderInfo = json_decode($cityPreviewOrder->order_info, true);
        $orderInfo['shop_order_id'] = $allOrderInfo['shop_order_id'];
        
        return $orderInfo;
    }

    public function handleNotify($cityPreviewOrder)
    {
        $allOrderInfo = json_decode($cityPreviewOrder->all_order_info, true);
        $instance = $this->cityServiceForm->getInstance();
        $enableDebug = $this->cityServiceForm->getEnabledDebug();

        if ($enableDebug) {
            // 分配骑手
            \Yii::$app->queue->delay(10)->push(new MtCityServiceJob([
                'preview_order_id' => $cityPreviewOrder->id,
                'mock_type' => 'arrange',
                'instance' => $instance,
            ]));
            // 骑手取货
            \Yii::$app->queue->delay(20)->push(new MtCityServiceJob([
                'preview_order_id' => $cityPreviewOrder->id,
                'mock_type' => 'pickup',
                'instance' => $instance,
            ]));
            // 配送完成
            \Yii::$app->queue->delay(300)->push(new MtCityServiceJob([
                'preview_order_id' => $cityPreviewOrder->id,
                'mock_type' => 'deliver',
                'instance' => $instance,
            ]));
        }
    }

    public function handleNotifyData(array $data)
    {

    }
}
