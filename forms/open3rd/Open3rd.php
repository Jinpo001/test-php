<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/9/25
 * Time: 14:53
 */

namespace app\forms\open3rd;

use luweiss\Wechat\WechatHttpClient;
use yii\base\BaseObject;

/**
 * 第三方平台授权流程
 * Class Open3rd
 * @package app\forms\open3rd
 */
class Open3rd extends BaseObject
{
    public $appId;
    public $appSecret;
    public $componentVerifyTicket;

    public $componentAccessToken;
    public $auth_type = 2;

    /**
     * 获取验证票据
     * @return mixed
     * @throws Open3rdException
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/api/component_verify_ticket.html
     */
    public function getComponentVerifyTicket()
    {
        $res = \Yii::$app->cache->get('WECHAT_OPEN_PLATFORM_COMPONENT_VERIFY_TICKET');
        if (!$res) {
            throw new Open3rdException('ComponentVerifyTicket Not Found');
        }
        $this->componentVerifyTicket = $res;
        return $res;
    }

    /**
     * 设置验证票据 component_verify_ticket 的有效时间为12小时,每10分钟推送一次
     * @param $ticket
     * @return bool
     */
    public function setComponentVerifyTicket($ticket)
    {
        \Yii::$app->cache->set('WECHAT_OPEN_PLATFORM_COMPONENT_VERIFY_TICKET', $ticket, 12 * 60 * 60);
        return true;
    }

    /**
     * 获取令牌
     * @return mixed
     * @throws Open3rdException
     * @throws \luweiss\Wechat\WechatException
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/api/component_access_token.html
     */
    public function getComponentAccessToken()
    {
        if ($this->componentAccessToken) {
            return $this->componentAccessToken;
        }
        if (!$this->appId) {
            throw new Open3rdException('appId 不能为空。');
        }
        if (!$this->appSecret) {
            throw new Open3rdException('appSecret 不能为空。');
        }
        $this->getComponentVerifyTicket();
        $api = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
        $data = [
            'component_appid' => $this->appId,
            'component_appsecret' => $this->appSecret,
            'component_verify_ticket' => $this->componentVerifyTicket,
        ];
        $res = $this->getClient()->post($api, json_encode($data));
        if (!$res || empty($res['component_access_token'])) {
            throw new Open3rdException('获取component_access_token失败');
        }
        $this->componentAccessToken = $res['component_access_token'];
        return $this->componentAccessToken;
    }

    /**
     * 预授权码
     * @return mixed
     * @throws Open3rdException
     * @throws \luweiss\Wechat\WechatException
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/api/pre_auth_code.html
     */
    public function getPreAuthCode()
    {
        $this->getComponentAccessToken();
        $api = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token={$this->componentAccessToken}";
        $data = [
            'component_appid' => $this->appId,
        ];
        $res = $this->getClient()->post($api, json_encode($data));
        if (!$res || empty($res['pre_auth_code'])) {
            throw new Open3rdException('获取pre_auth_code失败');
        }
        return $res['pre_auth_code'];
    }

    /**
     * 获取授权信息
     * @param string $auth_code
     * @return bool|mixed
     * @throws Open3rdException
     * @throws \luweiss\Wechat\WechatException
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/api/authorization_info.html
     */
    public function getAuthorizerInfo($auth_code = '')
    {
        $this->getComponentAccessToken();
        if (!$auth_code) {
            $auth_code = $this->getAuthCode();
        }
        if (!$auth_code) {
            return false;
        }

        $api = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token={$this->componentAccessToken}";
        $data =  [
            'component_appid' => $this->appId,
            'authorization_code' => $auth_code,
        ];
        $res = $this->getClient()->post($api, json_encode($data));
        if (!$res || empty($res['authorization_info'])) {
            throw new Open3rdException('获取授权信息失败');
        }
        return $res;
    }

    /**
     * 跳转授权
     * @return array|bool|mixed
     * @throws Open3rdException
     * @throws \luweiss\Wechat\WechatException
     */
    public function getAuthCode()
    {
        $auth_code = \Yii::$app->request->get('auth_code');
        $expires_in = \Yii::$app->request->get('expires_in');
        if ($auth_code && $expires_in) {
            return $auth_code;
        }
        $pre_auth_code = $this->getPreAuthCode();
        if (!$pre_auth_code) {
            return false;
        }
        $redirect_uri = urlencode(\Yii::$app->request->absoluteUrl . '&_mall_id=' . \Yii::$app->mall->id);
        $url = "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid={$this->appId}&pre_auth_code={$pre_auth_code}&redirect_uri={$redirect_uri}&auth_type={$this->auth_type}";
        echo '<script>location.href="' . $url . '"</script>';
        \Yii::$app->end();
    }

    /**
     * @return WechatHttpClient
     */
    protected function getClient()
    {
        return new WechatHttpClient();
    }
}
