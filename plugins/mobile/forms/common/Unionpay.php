<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/10/13
 * Time: 16:53
 */

namespace app\plugins\mobile\forms\common;


/**
 * https://open.unionpay.com/tjweb/acproduct/APIList?acpAPIId=334&apiservId=453&version=V2.2&bussType=0
 * Class Unionpay
 * @package app\plugins\mobile\forms\common
 */
class Unionpay
{
    //版本号
    private $version = '5.1.0';

    //编码方式
    private $encoding = 'UTF-8';

    //交易类型
    private $txnType = '01';

    //交易子类
    private $txnSubType = '01';

    //业务类型
    private $bizType = '000201';

    //签名方法
    private $signMethod = '01';

    //接入类型
    private $accessType = '0';

    //交易币种
    private $currencyCode = '156';

    //默认支付方式
    private $defaultPayType = '0001';

    //配置参数
    private $config;

    //form表单前缀
    private $name_pre = 'unionpay_form_';

    //开发模式
    private $dev;

    //银联网关
    private $api;

    //商户号
    private $merId;

    //签名证书路径
    private $signCertPath;

    //签名证书密码
    private $signCertPwd;

    //公钥证书路径
    private $verifyCertPath;

    //前台请求接口
    private $frontTransReq = 'frontTransReq.do';

    /**
     * 构造器
     * @method __construct
     * @param {array} $config 参数数组
     * @return {none}
     * @since 0.0.1
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->merId = $this->config['merId'];
        $this->signCertPath = $this->config['signCertPath'];
        $this->signCertPwd = $this->config['signCertPwd'];
        $this->dev = isset($this->config['dev']) && $this->config['dev'];
        if ($this->dev) {
            $this->api = 'https://gateway.test.95516.com/gateway/api/';
            $this->verifyCertPath = __DIR__ . '/unionpay_verify_dev.cer';
        } else {
            $this->api = 'https://gateway.95516.com/gateway/api/';
            $this->verifyCertPath = __DIR__ . '/unionpay_verify_prod.cer';
        }
    }

    /**
     * 获取类对象
     * @method sdk
     * @param {array} $config 参数数组
     * @return {none}
     * @since 0.0.1
     * @example static::sdk($config);
     */
    public static function sdk($config)
    {
        return new static($config);
    }

    /**
     * 验证签名
     * @method verifySign
     * @param {boolean} [$async=false] 是否为异步通知
     * @return {boolean}
     * @since 0.0.1
     * @example $this->verifySign($async);
     */
    public function verifySign($async = false)
    {
        if (empty($_POST) || !isset($_POST['signature']) || !isset($_POST['merId'])) {
            return false;
        }

        $signature = base64_decode($_POST['signature']);
        unset($_POST['signature']);

        $params_sha1x16 = sha1($this->getQeuryString($this->arrKsort($_POST)), false);
        $pkey = file_get_contents(\Yii::getAlias($this->verifyCertPath));

        return openssl_verify($params_sha1x16, $signature, $pkey, OPENSSL_ALGO_SHA1);
    }

    /**
     * 获取支付参数
     * @method getPayUrl
     * @param {string} $notify_url 异步通知地址
     * @param {string} $return_url 同步通知地址
     * @param {string} $orderId 商户订单号
     * @param {number} $txnAmt 交易金额
     * @param {int} [$expired_at=0] 过期时间
     * @return {string}
     * @since 0.0.1
     * @example $this->getPayUrl($notify_url, $return_url, $orderId, $txnAmt, $expired_at);
     */
    public function getPayUrl($notify_url, $return_url, $orderId, $txnAmt, $expired_at = 0)
    {
        $params = [
            'version' => $this->version,
            'encoding' => $this->encoding,
            'certId' => $this->getCertId(),
            'txnType' => $this->txnType,
            'txnSubType' => $this->txnSubType,
            'bizType' => $this->bizType,
            'frontUrl' => 'https://bd-test.zjhejiang.cn/web/index.php',
            'backUrl' => 'http://www.specialUrl.com',
            'signMethod' => $this->signMethod,
            'channelType' => $this->getChannelType(),
            'accessType' => $this->accessType,
            'merId' => $this->merId,
            'orderId' => $orderId,
            'txnTime' => date('YmdHis'),
            'txnAmt' => $txnAmt,
            'currencyCode' => $this->currencyCode,
            'defaultPayType' => $this->defaultPayType,
        ];

        //设置过期时间
        if ($expired_at > 0) {
            //$params['orderTimeout'] = ($expired_at - time()) * 1000;
            $params['payTimeout'] = date('YmdHis', $expired_at);
        }

        $params['signature'] = $this->sign($params);

        return $this->createPostForm($this->api . $this->frontTransReq, $params);
    }

    /**
     * 签名
     * @method sign
     * @param {array} $params 参数
     * @return {string}
     * @since 0.0.1
     */
    private function sign($params)
    {
        if (isset($params['transTempUrl'])) {
            unset($params['transTempUrl']);
        }

        $params_sha256x16 = hash('sha256', $this->getQeuryString($this->arrKsort($params)));
        $pkcs12 = file_get_contents(\Yii::getAlias($this->signCertPath));
        openssl_pkcs12_read($pkcs12, $certs, $this->signCertPwd);
        $pkey = $certs['pkey'];
        $sign_falg = openssl_sign($params_sha256x16, $signature, $pkey, 'sha256');

        return base64_encode($signature);
    }

    /**
     * 获取证书id
     * @method getCertId
     * @return {string}
     * @since 0.0.1
     */
    private function getCertId()
    {
        $pkcs12 = file_get_contents(\Yii::getAlias($this->signCertPath));
        openssl_pkcs12_read($pkcs12, $certs, $this->signCertPwd);
        $x509data = $certs['cert'];
        openssl_x509_read($x509data);
        $certdata = openssl_x509_parse($x509data);

        return $certdata['serialNumber'];
    }

    /**
     * 获取渠道类型
     * @method getChannelType
     * @return {string}
     * @since 0.0.1
     */
    private function getChannelType()
    {
        return '08';
    }

    /**
     * 创建待提交post表单
     * @method createPostForm
     * @param {string} $action 提交地址
     * @param {array} $params 参数
     * @return {string}
     * @since 0.0.1
     */
    private function createPostForm($action, $params)
    {
        $id = $this->name_pre . uniqId();
        $form = ['<form action="' . $action . '" method="post" name="' . $id . '">'];
        foreach ($params as $name => $value) {
            $form[] = '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        $form[] = '</form><script type="text/javascript">document.' . $id . '.submit();</script>';

        return implode('', $form);
    }

    /**
     * 获取queryString
     * @method getQeuryString
     * @param {array} $arr 需转换数组
     * @return {string}
     * @since 0.0.1
     */
    private function getQeuryString($arr)
    {
//        $string = '';
//        foreach ($arr as $i => $arg) {
//            if ($arr === null || $arg === '') {
//                continue;
//            } else {
//                $string .= ($i . '=' . $arg . '&');
//            }
//        }
//        // 去掉最后一个&字符
//        $string = substr($string, 0, -1);
//        return $string;
        return urldecode(http_build_query($arr));
    }

    /**
     * 对签名参数进行数组排序
     * @method arrKsort
     * @param {array} $arr 需排序数组
     * @return {array}
     * @since 0.0.1
     */
    private function arrKsort($arr)
    {
        ksort($arr);
        reset($arr);

        return $arr;
    }
}
