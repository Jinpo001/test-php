<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/9
 * Time: 4:17 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\models;

use app\forms\api\LoginUserInfo;
use app\forms\common\wechat\WechatFactory;
use app\models\UserInfo;
use app\models\WxappPlatform;
use app\plugins\wechat\forms\common\wechat\WechatConfig;
use app\plugins\wechat\Plugin;
use yii\helpers\Json;

class LoginForm extends \app\forms\api\LoginForm
{
    public function getUserInfo()
    {
        $postData = \Yii::$app->request->post();
        $wechatConfig = new WechatConfig();
        $wechat = (new Plugin())->getWechat();
        $third = WechatFactory::getThirdByMall(\Yii::$app->mall->id);
        if ($third) {
            $platform = WxappPlatform::getPlatform();
            $res = $wechatConfig->getThirdAccessToken([
                 'appid' => $third->authorizer_appid,
                 'code' => $postData['code'],
                 'grant_type' => 'authorization_code',
                 'component_appid' => $platform->appid,
                 'component_access_token' => $platform->component_access_token,
            ]);
        } else {
            $res = $wechatConfig->getAccessToken([
                 'appid' => $wechat->appId,
                 'secret' => $wechat->appSecret,
                 'code' => $postData['code']
            ]);
        }
        \Yii::warning(Json::encode($res, JSON_UNESCAPED_UNICODE));

        $scope = explode(',', $res['scope']);
        \Yii::warning($scope);
        $userInfo = new LoginUserInfo();
        if (!in_array('snsapi_userinfo', $scope)) {
            $userInfo->scope = 'auth_base';
            $userInfo->username = $res['openid'];
            $userInfo->platform = UserInfo::PLATFORM_WECHAT;
            $userInfo->platform_user_id = $res['openid'];
            $userInfo->user_platform = UserInfo::PLATFORM_WECHAT;
            $userInfo->user_platform_user_id = $res['openid'];
            return $userInfo;
        }
        $userInfoRes = $wechatConfig->getUserInfo([
            'access_token' => $res['access_token'],
            'openid' => $res['openid']
        ]);
        \Yii::warning($userInfoRes);
        $subscribe = $wechatConfig->getSubscribe([
            'access_token' => $wechat->getAccessToken(),
            'openid' => $res['openid']
        ]);
        \Yii::warning($subscribe);
        $userInfo->scope = 'auth_info';
        $userInfo->nickname = $userInfoRes['nickname'];
        $userInfo->username = $userInfoRes['openid'];
        $userInfo->avatar = $userInfoRes['headimgurl'] ?: \Yii::$app->request->hostInfo .
            \Yii::$app->request->baseUrl .
            '/statics/img/app/user-default-avatar.png';
        $userInfo->unionId = $userInfoRes['unionid'] ?? '';
        $userInfo->platform = UserInfo::PLATFORM_WECHAT;
        $userInfo->platform_user_id = $userInfoRes['openid'];
        $userInfo->user_platform = UserInfo::PLATFORM_WECHAT;
        $userInfo->user_platform_user_id = $userInfoRes['openid'];
        $userInfo->subscribe = $subscribe['subscribe'];
        return $userInfo;
    }
}
