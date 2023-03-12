<?php

namespace app\forms\common\order\send\city_service;

use CityService\Factory;
use GuzzleHttp\Client;
use app\forms\common\order\send\city_service\dada\Dada;
use app\forms\common\order\send\city_service\mk\Mk;
use app\forms\common\order\send\city_service\mt\Mt;
use app\forms\common\order\send\city_service\sf\Sf;
use app\forms\common\order\send\city_service\ss\Ss;
use app\forms\common\order\send\city_service\wechat\Wechat;
use app\models\CityService;

class CityServiceForm
{
    private $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function getCityService()
    {
        return $this->cityService;
    }

    public function getEnabledDebug()
    {
        $data = json_decode($this->cityService->data, true);

        $isDebug = isset($data['is_debug']) && $data['is_debug'] ? true : false;

        return $isDebug;
    }

    public function getModel()
    {
        if ($this->cityService->service_type == '微信') {
            return new Wechat($this);
        } else {
            $id = $this->cityService->distribution_corporation;
            switch ($id) {
                case 1:
                    return new Sf($this);
                    break;
                case 2:
                    return new Ss($this);
                    break;
                case 3:
                    return new Mt($this);
                    break;
                case 4:
                    return new Dada($this);
                    break;
                case 5:
                    return new Mk($this);
                    break;
                default:
                    throw new \Exception($id . '未定义');
                    break;
            }
        }
    }

    public function getInstance()
    {
        $model = $this->getModel();
        $instance = Factory::getInstance($model->getDivers(), $model->getConfig($this->cityService));

        return $instance;
    }

    public function getAddressInfo($lng, $lat)
    {
        $url = $url = 'https://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=OV7BZ-ZT3HP-6W3DE-LKHM3-RSYRV-ULFZV';
        $client = new Client();
        $res = $client->request('GET', $url, []);

        if ($res->getStatusCode() != 200) {
            throw new \Exception('用户收货地址异常1');
        }

        $data = json_decode($res->getBody(), true);
        if (!isset($data['result']['address_component']['city'])) {
            throw new \Exception('用户收货地址异常2');
        }
        $city = $data['result']['address_component']['city'];
        $new_city = substr($city, 0, strlen($city) - 3);

        return [
            'city' => $city,
            'new_city' => $new_city,
        ];
    }
}
