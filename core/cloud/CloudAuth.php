<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:20:00
 */


namespace app\core\cloud;


class CloudAuth extends CloudBase
{
    public $classVersion = '4.2.10';
    private $authInfo;

    public function getAuthInfo($refreshCache = false)
    {
        return [
            'host' => [
                'name' => 'localhost',
                'protocol' => 'http://',
                'domain' => 'localhost',
                'token' => '',
                'account_num' => -1,
                'status' => 1,
                'host_ips' => [
                    [
                        'ip' => '127.0.0.1',
                    ],
                ],
            ],
        ];
        $this->authInfo = $this->httpGet('/mall/site/info');
        return $this->authInfo;
    }
}
