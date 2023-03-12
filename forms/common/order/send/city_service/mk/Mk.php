<?php

namespace app\forms\common\order\send\city_service\mk;

use GuzzleHttp\Client;
use app\forms\common\order\send\city_service\BaseCityService;

class Mk extends BaseCityService
{
    public function getDivers()
    {
        return 'mk';
    }

    public function getName()
    {
        return '同城速送';
    }

    public function getConfig()
    {
        $cityService = $this->cityServiceForm->getCityService();
        $data = json_decode($cityService->data, true);
        return [
            'token' => $this->getToken($cityService->mall_id, $data['domain'], $data['appsecret'], $data['appkey']),
            'shop_id' => $cityService->mall_id,// 商城ID
            'base_url' => $data['domain'],
            'debug' => $data['is_debug'] ? true : false
        ];
    }

    public function preOrderResult(array $result)
    {
    	$result['fee'] = number_format($result['data']['total_price'], 2);

    	return $result;
    }
    
    public function addOrderResult(array $result)
    {
    	return $result;
    }

    public function preOrderData($data): array
    {
        return [
            'fromcoord' => $data['receiver']['lat'] . ',' . $data['receiver']['lng'],
            'tocoord' => $data['sender']['lat'] . ',' .$data['sender']['lng'],
        ];
    }

    public function addOrderData($cityPreviewOrder): array
    {
        $resultData = json_decode($cityPreviewOrder->result_data, true);
        $orderInfo = json_decode($cityPreviewOrder->all_order_info, true);
        $config = $this->getConfig();
        $notifyUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/msg-notify/mk.php';

        $goods = [];
        foreach ($orderInfo['cargo']['goods_detail']['goods'] as $key => $value) {
            $goods[] = [
                'name' => $value['good_name'],
                'price' => $value['good_price'],
                'num' => $value['good_count'],
            ];
        }

        $array = [
            'token' => $config['token'],
            'shop_id' => $config['shop_id'],
            'goods_name' => $orderInfo['shop']['goods_name'],
            'goods' => json_encode($goods, JSON_UNESCAPED_UNICODE),
            'pick_time' => time(),
            'remark' => '商品总数: ' . $orderInfo['shop']['goods_count'],
            'address' => json_encode([
                'begin_detail' => $orderInfo['sender']['address_detail'],
                'begin_address' => $orderInfo['sender']['address'],
                'begin_lat' => $orderInfo['sender']['lat'],
                'begin_lng' => $orderInfo['sender']['lng'],
                'begin_username' => $orderInfo['sender']['name'],
                'begin_phone' => $orderInfo['sender']['phone'],
                'end_detail' => $orderInfo['receiver']['address_detail'],
                'end_address' => $orderInfo['receiver']['address'],
                'end_lat' => $orderInfo['receiver']['lat'],
                'end_lng' => $orderInfo['receiver']['lng'],
                'end_username' => $orderInfo['receiver']['name'],
                'end_phone' => $orderInfo['receiver']['phone'],
            ], JSON_UNESCAPED_UNICODE),
            'pay_price' => $resultData['data']['total_price'],
            'total_price' => $resultData['data']['total_price'],
            'notify_url' => $notifyUrl,
        ];
        
    	return $array;
    }

    public static function getToken($mall_id, $baseUrl, $token, $appId) {
        try {
            $url = $baseUrl . '/addons/make_speed/core/public/index.php/apis/v2/get_token';
            $key = 'MK_TOKEN_' . $mall_id;
            $apiToken = \Yii::$app->redis->get($key);
            
            if ($apiToken) {
                $isValid = self::checkToken($baseUrl, $apiToken);
                if ($isValid) {
                    return $apiToken;
                }
            }

            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            $response =  $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'token' => $token,
                    'appid' => $appId
                ], JSON_UNESCAPED_UNICODE),
            ])->getBody();

            $data = json_decode($response->getContents(), true);

            \Yii::$app->redis->set($key, $data['token']);
            \Yii::$app->redis->expire($key, 60 * 60 * 60);

            return $data['token'];
        }catch(\Exception $exception) {
            $code = $exception->getCode();
            if ($code == 401) {
                throw new \Exception('无权限，请检查同城速送配置参数是否正确');
            }
            throw new \Exception($exception->getMessage());
        }
    }

    // 检测token是否有效
    private static function checkToken($baseUrl, $token)
    {
        $url = $baseUrl . '/addons/make_speed/core/public/index.php/apis/v2/verify_token';

        $client = new Client([
            'verify' => false,
            'timeout' => 30,
        ]);

        $response =  $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'token' => $token,
            ], JSON_UNESCAPED_UNICODE),
        ])->getBody();

        $data = json_decode($response->getContents(), true);

        return $data['isValid'];
    }

    public function handleNotify($cityPreviewOrder)
    {
        
    }

    public function handleNotifyData(array $data)
    {
        
    }
}
