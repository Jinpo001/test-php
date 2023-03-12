<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/9/25
 * Time: 9:40
 */

namespace app\plugins\wxapp\forms;

use app\models\WxappPlatform;
use app\plugins\wxapp\models\WxappWxminiprograms;
use luweiss\Wechat\Wechat;

class ThirdWechat extends Wechat
{
    public $platform;

    public function __construct($config = [])
    {
        $this->platform = WxappPlatform::getPlatform();
        parent::__construct($config);
    }

    /**
     * @param bool $refresh
     * @return string
     * @throws \Exception
     */
    public function getAccessToken($refresh = false)
    {
        $miniprogram = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($miniprogram->authorizer_expires < time()) {
            $miniprogram->updateAuthorizerAccessToken(
                $this->platform->appid,
                $this->platform->component_access_token
            );
        }
        return $miniprogram->authorizer_access_token;
    }

    public function jsCodeToSession($code)
    {
        $api = "https://api.weixin.qq.com/sns/component/jscode2session?appid={$this->appId}&js_code={$code}&grant_type=authorization_code&component_appid={$this->platform->appid}&component_access_token={$this->platform->component_access_token}";
        return $this->getClient()->get($api);
    }
}
