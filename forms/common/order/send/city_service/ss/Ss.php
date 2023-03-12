<?php

namespace app\forms\common\order\send\city_service\ss;

use app\forms\common\order\send\city_service\BaseCityService;

class Ss extends BaseCityService
{
    public function getDivers()
    {
        return 'ss';
    }

    public function getName()
    {
        return '闪送';
    }

    public function getConfig()
    {
        $cityService = $this->cityServiceForm->getCityService();
        $data = json_decode($cityService->data, true);
        return [
            'clientId' => $data['appkey'],
            'secret' => $data['appsecret'],
            'shopId' => $cityService->shop_no,
            'debug' => isset($data['is_debug']) && $data['is_debug'] ? true : false,
        ];
    }

    public function preOrderResult(array $result)
    {
    	$result = $result['data'];
        $result['fee'] = number_format($result['totalFeeAfterSave'] / 100, 2);

    	return $result;
    }
    
    public function addOrderResult(array $result)
    {
    	return $result;
    }

    public function preOrderData($data): array
    {
        $data['order_info']['is_direct_delivery'] = 1;

        $this->dealPos($data);
        $receiver = [
            "orderNo" => $data['shop_order_id'],
            "toAddress" => $data['receiver']['address'] ?? '',
            "toAddressDetail" => $data['receiver']['address_detail'],
            "toLatitude" => $data['receiver']['lat'],
            "toLongitude" => $data['receiver']['lng'],
            "toReceiverName" => $data['receiver']['name'],
            "toMobile" => $data['receiver']['phone'],
            "goodType" => $data['product_type'] ?: 10,
            "weight" => $data['cargo']['goods_weight'] > 1 ? $data['cargo']['goods_weight'] : 1,
        ];

        $json = [
            "cityName" => $data['sender']['city'],
            "sender" => [
                "fromAddress" => $data['sender']['address'],
                "fromAddressDetail" => $data['sender']['address_detail'],
                "fromSenderName" => $data['sender']['name'],
                "fromMobile" => $data['sender']['phone'],
                "fromLatitude" => $data['sender']['lat'],
                "fromLongitude" => $data['sender']['lng'],
            ],
            "receiverList" => [
                $receiver,
            ],
            "appointType" => 0,
        ];

        // 测试模式下参数修改
        if ($this->cityServiceForm->getEnabledDebug()) {
            $json['cityName'] = '上海市';
        }

        return [
            'data' => json_encode($json, JSON_UNESCAPED_UNICODE),
        ];
    }

    public function addOrderData($cityPreviewOrder): array
    {
        $resultData = json_decode($cityPreviewOrder->result_data, true);
        $allOrderInfo = json_decode($cityPreviewOrder->all_order_info, true);
    	$array = [
            'issOrderNo' => $resultData['orderNumber'],
        ];

        return [
            'shop_order_id' => $allOrderInfo['shop_order_id'],
            'data' => json_encode($array, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * 腾讯地图---->百度地图
     * @param double $lat 纬度
     * @param double $lng 经度
     */
    private function Convert_GCJ02_To_BD09($lat, $lng)
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $lng = $z * cos($theta) + 0.0065;
        $lat = $z * sin($theta) + 0.006;
        return array('lng' => $lng, 'lat' => $lat);
    }

    /**
     * 转换腾讯地图坐标为百度坐标
     * @param $data
     */
    private function dealPos(&$data)
    {
        $receiverPos = $this->Convert_GCJ02_To_BD09($data['receiver']['lat'], $data['receiver']['lng']);
        $data['receiver']['lat'] = $receiverPos['lat'];
        $data['receiver']['lng'] = $receiverPos['lng'];
        $senderPos = $this->Convert_GCJ02_To_BD09($data['sender']['lat'], $data['sender']['lng']);
        $data['sender']['lat'] = $senderPos['lat'];
        $data['sender']['lng'] = $senderPos['lng'];
    }

    public function handleNotify($cityPreviewOrder)
    {
        
    }

    public function handleNotifyData(array $data)
    {

    }
}
