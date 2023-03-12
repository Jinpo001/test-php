<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/21
 * Time: 4:08 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\common\wechat;


use luweiss\Wechat\WechatBase;
use luweiss\Wechat\WechatException;
use luweiss\Wechat\WechatHttpClient;
use yii\helpers\Json;

class WechatConfig extends WechatBase
{
    /**
     * WechatPay constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        foreach ($config as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getClientResult($result)
    {
        if (isset($result['errcode']) && $result['errcode'] !== 0) {
            $msg = 'errcode: ' . $result['errcode'] . ', errmsg: ' . $result['errmsg'];
            throw new WechatException($msg, 0, null, $result);
        }
        return $result;
    }

    /**
     * @param $api
     * @param $args
     * @return array
     * @throws WechatException
     */
    protected function send($api, $args)
    {
        $res = $this->getClient()->setDataType(WechatHttpClient::DATA_TYPE_JSON)->post($api, $args);
        return $this->getClientResult($res);
    }

    public function getticket($args)
    {
        $api = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi';
        return $this->sendByGet($api, $args);
    }

    public function sendByGet($api, $args)
    {
        $res = $this->getClient()->setDataType(WechatHttpClient::DATA_TYPE_JSON)->get($api, $args);
        return $this->getClientResult($res);
    }

    /**
     * @param $args
     * @return array
     * 获取网页授权access_token
     */
    public function getAccessToken($args)
    {
        $api = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $args['grant_type'] = 'authorization_code';
        return $this->sendByGet($api, $args);
    }

    public function getThirdAccessToken($args)
    {
        $api = 'https://api.weixin.qq.com/sns/oauth2/component/access_token';
        return $this->sendByGet($api, $args);
    }

    /**
     * @param $args
     * @return array
     * 获取用户信息
     */
    public function getUserInfo($args)
    {
        $api = 'https://api.weixin.qq.com/sns/userinfo';
        $args['lang'] = 'zh_CN';
        return $this->sendByGet($api, $args);
    }

    /**
     * @param $args
     * @return array
     * 获取用户信息
     */
    public function getSubscribe($args)
    {
        $api = 'https://api.weixin.qq.com/cgi-bin/user/info';
        $args['lang'] = 'zh_CN';
        return $this->sendByGet($api, $args);
    }

    public function getQrcode($args)
    {
        $api = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $args['access_token'];
        $res = $this->send($api, Json::encode($args, JSON_UNESCAPED_UNICODE));
        if (!isset($res['ticket'])) {
            return $res;
        }
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($res['ticket']);
    }
}
