<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/21
 * Time: 4:26 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\api;


use app\core\response\ApiCode;
use app\forms\common\wechat\WechatFactory;
use app\models\UserInfo;
use app\models\UserPlatform;
use app\models\WxappPlatform;
use app\plugins\wechat\forms\common\wechat\WechatConfig;
use app\plugins\wechat\forms\Model;
use app\plugins\wechat\Plugin;

/**
 * Class WechatForm
 * @package app\plugins\wechat\forms\api
 * @property Plugin $plugin
 */
class WechatForm extends Model
{
    public $plugin;
    public $url;
    public $scope;
    public $refresh;

    public function init()
    {
        parent::init();
        $this->plugin = new Plugin();
    }

    public function rules()
    {
        return [
            [['url', 'refresh', 'scope'], 'trim'],
            [['url', 'refresh', 'scope'], 'string'],
            ['refresh', 'default', 'value' => false],
        ];
    }

    public function result()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $args = [
                'jsapi_ticket' => $this->getticket($this->refresh),
                'noncestr' => \Yii::$app->security->generateRandomString(),
                'timestamp' => time() . '',
                'url' => rtrim($this->url, '/')
            ];
            $args['signature'] = $this->signature($args);
            $args['appid'] = $this->getAppId();
            return $this->success($args);
        } catch (\Exception $exception) {
            return $this->fail(['msg' => $exception->getMessage()]);
        }
    }

    protected function getticket($refresh = false)
    {
        $cacheKey = 'CHECK_TICKET_OF_TOKEN-' . $this->getAppId();
        $ticket = \Yii::$app->cache->get($cacheKey);
        if ($ticket && !$refresh) {
            return $ticket;
        }
        $accessToken = $this->plugin->getAccessToken();
        $res = (new WechatConfig())->getticket([
            'access_token' => $accessToken
        ]);
        \Yii::$app->cache->set($cacheKey, $res['ticket'], 7000);
        return $res['ticket'];
    }

    protected function getAppId()
    {
        return $this->plugin->getWechat()->appId;
    }

    protected function signature($args)
    {
        $string = '';
        foreach ($args as $key => $value) {
            $string .= $key . '=' . $value;
            if ($key !== 'url') {
                $string .= '&';
            }
        }
        return sha1($string);
    }

    public function loginUrl()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            if (!$this->getAppId()) {
                return $this->fail(['msg' => 'appid为空']);
            }
        } catch (\Exception $exception) {
            return $this->fail([]);
        }
        $uri = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        $args = [
            'appid' => $this->getAppId(),
            'redirect_uri' => urlencode($this->url),
            'response_type' => 'code',
            'scope' => $this->scope,
            'state' => \Yii::$app->mall->id,
        ];
        $third = WechatFactory::getThirdByMall(\Yii::$app->mall->id);
        if ($third) {
            $platform = WxappPlatform::getPlatform();
            $args['component_appid'] = $platform->appid;
        }
        $params = '';
        foreach ($args as $key => $item) {
            $params .= $key . '=' . $item . '&';
        }
        $url = $uri . '?' . rtrim($params, '&') . '#wechat_redirect';
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'appid' => $this->getAppId(),
                'state' => \Yii::$app->mall->id,
                'url' => $url
            ]
        ];
    }

    public function updateSubscribe()
    {
        $wechatConfig = new WechatConfig();
        $wechat = (new Plugin())->getWechat();
        $user = \Yii::$app->user->identity;
        $userPlatform = UserPlatform::findOne([
            'mall_id' => $user->mall_id, 'user_id' => $user->id, 'platform' => UserInfo::PLATFORM_WECHAT
        ]);
        $userInfoRes = $wechatConfig->getSubscribe([
            'access_token' => $wechat->getAccessToken(),
            'openid' => $userPlatform->platform_id
        ]);
        $userPlatform->subscribe = $userInfoRes['subscribe'];
        $userPlatform->save();
        return $this->success(['subscribe' => $userInfoRes['subscribe']]);
    }
}
