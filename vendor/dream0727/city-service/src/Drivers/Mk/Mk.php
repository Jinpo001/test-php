<?php

namespace CityService\Drivers\Mk;

use CityService\AbstractCityService;
use CityService\CityServiceInterface;
use CityService\Drivers\Mk\Exceptions\MkException;
use CityService\Drivers\Mk\Response\MkResponse;
use CityService\Exceptions\HttpException;
use CityService\ResponseInterface;
use GuzzleHttp\Client;

class Mk extends AbstractCityService implements CityServiceInterface
{
    const BASE_URI = '';
    const TEST_URI = '';

    public function getAllImmeDelivery(): \CityService\ResponseInterface
    {
        // TODO: Implement getAllImmeDelivery() method.
    }

    /**
     * 预创建订单
     * https://newopen.imdada.cn/#/development/file/readyAdd?_k=qwld89
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function preAddOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = '/addons/make_speed/core/public/index.php/apis/v2/get_delivery_price';

        $result = $this->get($path, $data);

        return new MkResponse(json_decode($result, true));
    }

    /**
     * 创建订单
     * http://commit-openic.sf-express.com/open/api/docs/index#/apidoc
     * @param array $data
     * @return ResponseInterface
     * @throws HttpException
     * @throws \CityService\Exceptions\CityServiceException
     */
    public function addOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = '/addons/make_speed/core/public/index.php/apis/v2/create_order';

        $result = $this->post($path, $data);

        return new MkResponse(json_decode($result, true));
    }

    public function reOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement reOrder() method.
    }

    public function addTip(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement addTip() method.
    }

    public function preCancelOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement preCancelOrder() method.
    }

    public function cancelOrder(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement cancelOrder() method.
    }

    public function abnormalConfirm(array $data = []): \CityService\ResponseInterface
    {
        // TODO: Implement abnormalConfirm() method.
    }

    public function getOrder(array $data = []): \CityService\ResponseInterface
    {
        $path = '/addons/make_speed/core/public/index.php/apis/v2/get_order_detail';

        $result = $this->post($path, $data);

        return new MkResponse(json_decode($result, true));
    }

    /**
     * 模拟配送测试
     * https://peisong.meituan.com/open/doc#section3-2
     * @param  [type] $mockType [description]
     * @param  array  $data     [description]
     * @return [type]           [description]
     */
    public function mockUpdateOrder(array $data = [], array $params = []): \CityService\ResponseInterface
    {
        if (!isset($params['mock_type'])) {
            throw new MkException('mock_type异常');
        }

        switch ($params['mock_type']) {
            // 模拟接单
            case 'accept':
                $path = '/api/order/accept';
                break;
            // 模拟取货
            case 'fetch':
                $path = '/api/order/fetch';
                break;
            // 模拟完成
            case 'finish':
                $path = '/api/order/finish';
                break;
            // 模拟取消
            case 'cancel':
                $path = '/api/order/cancel';
                break;
            // 模拟订单异常
            case 'back':
                $path = '/api/order/delivery/abnormal/back';
                break;
            default:
                throw new MkException('未知模拟类型');
                break;
        }

        $result = $this->post($path, $data);

        return new MkResponse(json_decode($result, true));
    }

    /**
     * 签名验证
     * https://newopen.imdada.cn/#/quickStart/develop/safety?_k=s9qqt0
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function makeSign($data)
    {
        //1.升序排序
        ksort($data);

        //2.字符串拼接
        $args = "";
        foreach ($data as $key => $value) {
            $args .= $key . $value;
        }
        $args = $this->getConfig('appSecret') . $args . $this->getConfig('appSecret');
        //3.MD5签名,转为大写
        $sign = strtoupper(md5($args));

        return $sign;
    }

    /**
     * https://newopen.imdada.cn/#/quickStart/develop/mustRead?_k=dt6eiy
     * @param  [type] $path [description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function post($path, array $data = [])
    {
        try {
            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            if ($this->getConfig('debug')) {
                throw new MkException('同城速送不支持测试模式');
            }

            $baseUrl = $this->getConfig('base_url');
            $url = $baseUrl . $path;

            return $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ])->getBody();

        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage());
        }
    }

    /**
     * https://api.99make.com/#414212
     * @param  [type] $path [description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function get($path, array $data = [])
    {
        try {
            // 系统参数
            $data['token'] = $this->getConfig('token');
            $data['shop_id'] = $this->getConfig('shop_id');

            $client = new Client([
                'verify' => false,
                'timeout' => 30,
            ]);

            if ($this->getConfig('debug')) {
                throw new MkException('同城速送不支持测试模式');
            }

            $baseUrl = $this->getConfig('base_url');
            $url = $baseUrl . $path;
            $url = $this->buildParams($url, $data);

            return $client->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ])->getBody();

        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage());
        }
    }

    private function buildParams($url, $array)
    {
        $query = http_build_query($array);
        $url = trim($url, '?');
        $url = trim($url, '&');
        if (mb_stripos($url, '?')) {
            return $url . '&' . $query;
        } else {
            return $url . '?' . $query;
        }
    }
}
