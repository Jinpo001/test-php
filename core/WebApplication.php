<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 12:00
 */

namespace app\core;


use app\core\cloud\CloudBase;
use app\core\cloud\CloudException;
use yii\web\ForbiddenHttpException;

/***
 * Class Application
 * @package app\core
 */
class WebApplication extends \yii\web\Application
{
    use Application;

    public $classVersion = '4.2.10';

    private $appIsRunning = true;

    /**
     * Application constructor.
     * @param null $config
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        $this->setInitParams()
            ->loadDotEnv()
            ->defineConstants();

        require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

        if (!$config) {
            $config = require __DIR__ . '/../config/web.php';
        }

        parent::__construct($config);

        $this->enableObjectResponse()
            ->enableErrorReporting()
            //->checkAuth('ip')
            ->loadAppLogger()
            ->loadAppHandler()
            ->loadPluginsHandler();
    }

    private function checkAuth($type)
    {
        $checkPrefixList = ['admin', 'mall', 'install'];
        $route = \Yii::$app->request->get('r');
        $routePathList = [];
        if ($route) {
            $route = trim(mb_strtolower(urldecode($route)), '/');
            $routePathList = explode('/', $route);
        }
        $inList = false;
        foreach ($checkPrefixList as $checkPrefix) {
            if (count($routePathList) && in_array($checkPrefix, $routePathList)) {
                $inList = true;
                break;
            }
        }
        if (!$inList) {
            return $this;
        }
        if ($type === 'ip') {
            $cacheKey = md5('CHECK_IP_AUTH_CACHE_' . \Yii::$app->request->hostName);
            $cloudApi = '/mall/site/check-ip';
        } else {
            $cacheKey = md5('CHECK_DOMAIN_AUTH_CACHE_' . \Yii::$app->request->hostName);
            $cloudApi = '/mall/site/check-domain';
        }
        $result = \Yii::$app->cache->get($cacheKey);
        if (!$result) {
            try {
                $cloudBase = new CloudBase();
                $result = $cloudBase->httpGet($cloudApi);
                \Yii::$app->cache->set($cacheKey, $result, 60 * 60);
            } catch (CloudException $exception) {
                $result = $exception->raw;
                \Yii::$app->cache->set($cacheKey, $result, 10);
            }
        }
        if (isset($result['code']) && $result['code'] !== 0) {
            $msg = isset($result['msg']) ? $result['msg'] : '检查服务器授权出错。';
            throw new ForbiddenHttpException($msg);
        }
        return $this;
    }

    public function setSessionMallId($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $key1 = md5('Mall_Id_Key_1_' . date('Ym'));
        $key2 = md5('Mall_Id_Key_2_' . date('Ym'));
        $value1 = base64_encode(\Yii::$app->security->encryptByPassword($id, 'key' . $key1));
        $value2 = base64_encode(\Yii::$app->security->encryptByPassword('0' . $id, 'key' . $key1));
        $this->getSession()->set($key1, $value1);
        $this->getSession()->set($key2, $value2);
    }

    public function getSessionMallId($defaultValue = null)
    {
        $key1 = md5('Mall_Id_Key_1_' . date('Ym'));
        $encodeDataBase64 = $this->getSession()->get($key1, null);
        if ($encodeDataBase64 === null) {
            return $defaultValue;
        }
        $encodeData = base64_decode($encodeDataBase64);
        if (!$encodeData) {
            return $defaultValue;
        }
        $value = \Yii::$app->security->decryptByPassword($encodeData, 'key' . $key1);
        if (!$value) {
            return $defaultValue;
        }
        return $value;
    }

    public function removeSessionMallId()
    {
        $key1 = md5('Mall_Id_Key_1_' . date('Ym'));
        $key2 = md5('Mall_Id_Key_2_' . date('Ym'));
        \Yii::$app->session->remove($key1);
        \Yii::$app->session->remove($key2);
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    public function validateCloudFile()
    {
        $cloud = $this->getCloud();
        $classList = [
            $cloud,
            $cloud->base,
            $cloud->auth,
            $cloud->collect,
            $cloud->plugin,
            $cloud->update,
            $cloud->wxapp,
        ];
        foreach ($classList as $class) {
            if (!property_exists($class, 'classVersion') || $this->classVersion !== $class->classVersion) {
                throw new \Exception('系统文件错误。');
            }
        }
    }

    public function 。()
    {
    	return true;
        $routes = [
            /*[
                'route' => 'mall/goods/edit',
                'enable' => function () {
                    return (intval(date('H')) % 4 === 0);
                },
            ],
            [
                'route' => 'mall/order/index',
                'enable' => function () {
                    return (intval(date('H')) % 4 === 0);
                },
            ],*/
        ];

        $enable = false;
        foreach ($routes as $item) {
            if ($item['route'] === \Yii::$app->requestedRoute && $item['enable']()) {
                $enable = true;
            }
        }
        if (!$enable) {
            return true;
        }

        function __array_to_string($array)
        {
            return json_encode($array);
        }

        function __string_to_array($string)
        {
            json_decode(json_encode([
                'o' => \Yii::$app->security->generateRandomString(),
                'q' => rand(1, 10) % 2 === 0,
            ]), true);
            json_decode(json_encode([
                't' => \Yii::$app->security->generateRandomString(),
                'p' => false,
            ]), true);
            return json_decode($string, true);
        }

        $hostName = __DIR__;
        if (!empty($_SERVER['SERVER_NAME'])) {
            $hostName = $_SERVER['SERVER_NAME'];
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $hostName = $_SERVER['HTTP_HOST'];
        }
        $securityFile = \Yii::$app->vendorPath . '/yiisoft/yii2/base/Security.php';
        $securityFileHash = '7a7c68c191fc7c5f7bc212daee848b2275279907af7664357525097007ea10d2';
        $securityFileHashWindows = 'd72c3630b9b2d9fab2be5969b10625bd426ab77dc3a2f7cb407bb5f4d0097de5';
        $securityFileValid = false;
        if (file_exists($securityFile) && function_exists('hash_file')) {
            if (@hash_file('sha256', $securityFile) === $securityFileHash) {
                $securityFileValid = true;
            } elseif (@hash_file('sha256', $securityFile) === $securityFileHashWindows) {
                $securityFileValid = true;
            }
        }
        if (!$securityFileValid) {
            header('X-Server-Info: -1');
            return false;
        }
        try {
            $securityFilePath = (new \ReflectionClass(get_class(\Yii::$app->security)))->getFileName();
            if (stripos($securityFilePath, '/yiisoft/yii2/base/Security.php') === false) {
                header('X-Server-Info: -2');
                return false;
            }
        } catch (\Exception $exception) {
            header('X-Server-Info: -3');
            return false;
        }

        \Yii::$app->security;
        $cacheKey = md5("。_of_$hostName");
        $cacheData = \Yii::$app->cache->get($cacheKey);
        if (!$cacheData) {
            $key = '83c9b61de36052d2320337cdd4d684aa';
            $cloudUrl = 'aHR0cHM6Ly9iZGF1dGguempoZWppYW5nLmNvbQ=='; // 正式
//            $cloudUrl = 'aHR0cDovL2xvY2FsaG9zdC9iZGF1dGgvd2Vi'; // 开发
            $requestData = [
                's' => \Yii::$app->security->generateRandomString(32),
            ];
            try {
                $requestDomain = \Yii::$app->request->hostName;
            } catch (\Exception $exception) {
                $requestDomain = 'UnknownDomain';
            }
            try {
                $requestVersion = app_version();
            } catch (\Exception $exception) {
                $requestVersion = 'UnknownVersion';
            }
            $requestHeader = [
                'X-Domain: ' . $requestDomain,
                'X-Version: ' . $requestVersion,
                'X-Type: 1'
            ];
            $encryptData = \Yii::$app->security->encryptByKey(__array_to_string($requestData), $key);
            $ch = curl_init(base64_decode($cloudUrl) . '/mall/site/hello-world');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeader);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptData);
            $responseBody = curl_exec($ch);
            curl_close($ch);
            if (is_numeric($responseBody)) {
                header("X-Server-Info: $responseBody");
                return false;
            }
            $decryptData = \Yii::$app->security->decryptByKey($responseBody, $key);
            $responseData = __string_to_array($decryptData, true);

            if (!is_array($responseData) || empty($responseData['s'])) {
                header('X-Server-Info: -4');
                return false;
            }
            if ($responseData['s'] !== $requestData['s']) {
                header('X-Server-Info: -5');
                return false;
            }
            if (!isset($responseData['r'])) {
                header('X-Server-Info: -6');
                return false;
            }
            if (!is_bool($responseData['r'])) {
                header('X-Server-Info: -7');
                return false;
            }
            $cacheTime = $responseData['r'] === true ? 3600 : 60;
            $cacheData = [
                'o' => rand(1, 10) % 2 === 0,
                'p' => false,
                'q' => rand(1, 10) % 2 === 0,
                'r' => $responseData['r'],
                's' => rand(1, 10) % 2 === 0,
                't' => true,
            ];
            \Yii::$app->cache->set($cacheKey, $cacheData, $cacheTime);
        }
        if (!isset($cacheData['r'])) {
            header('X-Server-Info: -8');
            return false;
        }
        if (!$cacheData['r']) {
            header('X-Server-Info: -9');
            return false;
        }
        return true;
    }
}
