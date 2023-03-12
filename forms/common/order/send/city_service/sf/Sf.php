<?php

namespace app\forms\common\order\send\city_service\sf;

use app\forms\common\order\send\city_service\BaseCityService;

class Sf extends BaseCityService
{
    public function getDivers()
    {
        return 'sf';
    }

    public function getName()
    {
        return '顺丰同城急送';
    }

    public function getConfig()
    {
        $cityService = $this->cityServiceForm->getCityService();
        $data = json_decode($cityService->data, true);
        return [
            'dev_id' => $data['appkey'],
            'dev_key' => $data['appsecret'],
            'shop_id' => $cityService->shop_no,
        ];
    }

    public function preOrderResult(array $result)
    {
    	$result = $result['result'];
        $result['fee'] = number_format($result['total_price'] / 100, 2);

    	return $result;
    }
    
    public function addOrderResult(array $result)
    {
    	return $result;
    }

    public function preOrderData($data): array
    {
        $data = $this->setDebugData($data);

        return [
            'user_lng' => $data['receiver']['lng'],
            'user_lat' => $data['receiver']['lat'],
            'user_address' => $data['receiver']['address'] . ' ' . $data['receiver']['address_detail'],
            'weight' => $data['cargo']['goods_weight'] > 0 ? $data['cargo']['goods_weight'] * 1000 : 1000,
            'product_type' => $data['product_type'] ?: 99,
            'is_appoint' => 0,
            'pay_type' => 1,
            'is_insured' => 0,
            'is_person_direct' => 0,
            'push_time' => time(),
        ];
    }

    public function addOrderData($cityPreviewOrder): array
    {
    	// 下单时数据要用 预下单时的数据
        $data = json_decode($cityPreviewOrder->all_order_info, true);
        $data = $this->setDebugData($data);
        return [
            'shop_order_id' => $data['shop_order_id'],
            'order_source' => $data['shop_no'],
            'pay_type' => 1,
            'order_time' => time(),
            'is_appoint' => 0,
            'is_insured' => 0,
            'is_person_direct' => 0,
            'return_flag' => 511,
            'push_time' => time(),
            'version' => 1,
            'receive' => [
                'user_name' => $data['receiver']['name'],
                'user_phone' => $data['receiver']['phone'],
                'user_address' => $data['receiver']['address'] . ' ' . $data['receiver']['address_detail'],
                'user_lng' => $data['receiver']['lng'],
                'user_lat' => $data['receiver']['lat'],
            ],
            'order_detail' => [
                'total_price' => $data['cargo']['goods_value'] * 100,
                'product_type' => $data['product_type'] ?: 99,
                'weight_gram' => $data['cargo']['goods_weight'] > 0 ? $data['cargo']['goods_weight'] * 1000 : 1000,
                'product_num' => $data['shop']['goods_count'],
                'product_type_num' => 1,
                'product_detail' => [
                    'product_name' => $data['shop']['goods_name'],
                    'product_num' => $data['shop']['goods_count'],
                ]
            ],
        ];
    }

    private function setDebugData($data)
    {
        $enableDebug = $this->cityServiceForm->getEnabledDebug();
        if ($enableDebug) {
            $data['receiver']['lng'] = '116.334424';
            $data['receiver']['lat'] = '40.030177';
            $data['receiver']['address_detail'] = '北京北京市海淀区华润五彩城购物中心';
        }

        return $data;
    }

    public function handleNotify($cityPreviewOrder)
    {
        
    }

    public function handleNotifyData(array $data)
    {

    }
}
