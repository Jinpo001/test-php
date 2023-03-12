<?php

namespace app\forms\common\order\send\city_service\wechat;

use app\forms\common\order\send\city_service\BaseCityService;
use app\forms\common\order\send\job\CityServiceJob;
use app\plugins\wxapp\models\WxappConfig;

class Wechat extends BaseCityService
{
    public function getDivers()
    {
        return 'wechat';
    }

    public function getName()
    {
        return '微信';
    }

    public function getConfig()
    {
        $wxappConfig = WxappConfig::find()
            ->where(['mall_id' => \Yii::$app->mall->id])
            ->with(['service'])
            ->one();

        if (!$wxappConfig) {
            throw new \Exception('微信参数未配置');
        }

        $cityService = $this->cityServiceForm->getCityService();
        $data = json_decode($cityService->data, true);

        $config = [
            'appId' => $wxappConfig->appid,
            'appSecret' => $wxappConfig->appsecret
        ];

        // 测试参数
        if ($data['is_debug']) {
            $config['deliveryId'] = 'TEST';
            $config['shopId'] = 'test_shop_id';
            $config['deliveryAppSecret'] = 'test_app_secrect';
        } else {
            $config['deliveryId'] = $this->getDeliveryId($cityService->distribution_corporation);
            $config['shopId'] = $data['appkey'];
            $config['deliveryAppSecret'] = $data['appsecret'];
        }

        return $config;
    }

    private function getDeliveryId($id)
    {
        switch ($id) {
            case 1:
                return 'SFTC';
                break;
            case 2:
                return 'SS';
                break;
            case 3:
                return 'MTPS';
                break;
            case 4:
                return 'DADA';
                break;
            default:
                throw new \Exception($id . '未定义');
                break;
        }
    }

    public function preOrderResult(array $result)
    {
        $result['fee'] = number_format($result['fee'], 2);

    	return $result;
    }
    
    public function addOrderResult(array $result)
    {
    	return $result;
    }

    public function preOrderData($data): array
    {
        $cityService = $this->cityServiceForm->getCityService();
        switch ($this->getDeliveryId($cityService->distribution_corporation)) {
            // 顺丰
            case 'SFTC':
                break;
            // 闪送
            case 'SS':
                $data['order_info']['is_direct_delivery'] = 1;
                break;
            // 达达
            case 'DADA':
                break;
            // 美团
            case 'MTPS':
                $id = isset($data['delivery_service_code']) ? (int)$data['delivery_service_code'] : 4002;
                $data['order_info']['delivery_service_code'] = $id;
                break;
            default:
                throw new \Exception('微信配送，未知配送公司');
                break;
        }
        return $data;
    }

    public function addOrderData($cityPreviewOrder): array
    {
        $orderInfo = json_decode($cityPreviewOrder->order_info, true);
    	return $orderInfo;
    }

    public function handleNotify($cityPreviewOrder)
    {
        $enableDebug = $this->cityServiceForm->getEnabledDebug();
        $instance = $this->cityServiceForm->getInstance();
        $allOrderInfo = json_decode($cityPreviewOrder->all_order_info, true);
        $shopOrderId = $allOrderInfo['shop_order_id'];

        // debug模式 开启模拟测试
        if ($enableDebug) {
            // 分配骑手
            \Yii::$app->queue->delay(10)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 102,
                'instance' => $instance,
            ]));
            // 骑手取货
            \Yii::$app->queue->delay(20)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 202,
                'instance' => $instance,
            ]));
            // 配送中
            \Yii::$app->queue->delay(30)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 301,
                'instance' => $instance,
            ]));
            // 配送完成
            \Yii::$app->queue->delay(40)->push(new CityServiceJob([
                'shopOrderId' => $shopOrderId,
                'waybillId' => $waybillId,
                'status' => 302,
                'instance' => $instance,
            ]));
        }
    }

    public function handleNotifyData(array $data)
    {

    }
}
