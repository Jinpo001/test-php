<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/9
 * Time: 4:17 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\models;

use app\forms\api\LoginUserInfo;
use app\models\User;
use app\models\UserInfo;
use app\models\UserPlatform;
use app\validators\ValidateCodeValidator;

class LoginForm extends \app\forms\api\LoginForm
{
    public $pic_captcha;
    public $password;
    public $mobile;
    public $captcha;
    public $type;
    public $validate_code_id;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['username'] = ['mobile', 'password'];
        $scenarios['mobile'] = ['mobile', 'captcha', 'validate_code_id'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['type'], 'required'],
            [['password', 'pic_captcha'], 'required', 'on' => ['username']],
            [['mobile', 'captcha', 'validate_code_id'], 'required', 'on' => ['mobile']],
            [['pic_captcha'], 'captcha', 'captchaAction' => 'site/pic-captcha', 'on' => ['username']],
            [['captcha'], ValidateCodeValidator::class,
                'mobileAttribute' => 'mobile',
                'validateCodeIdAttribute' => 'validate_code_id',
                'on' => ['mobile']
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => '密码',
            'mobile' => '手机号',
            'captcha' => '验证码',
            'pic_captcha' => '图形验证码',
            'type' => '登录类型',
        ];
    }

    public function getUserInfo()
    {
        $this->scenario = \Yii::$app->request->post('type');
        $this->attributes = \Yii::$app->request->get();
        $this->attributes = \Yii::$app->request->post();
        if (!$this->validate()) {
            throw new \Exception($this->getErrorMsg());
        }
        switch ($this->scenario) {
            case 'username':
                $userInfo = $this->usernameLogin();
                break;
            case 'mobile':
                $userInfo = $this->mobileLogin();
                break;
            default:
                throw new \Exception('未知的登录方式');
        }
        return $userInfo;
    }

    /**
     * @throws \Exception
     * 账号密码登录
     */
    private function usernameLogin()
    {
        $userPlatform = $this->getUser($this->mobile, UserInfo::PLATFORM_H5);
        if (!$userPlatform) {
            throw new \Exception('用户不存在，请先注册');
        }

        if (!\Yii::$app->getSecurity()->validatePassword($this->password, $userPlatform->password)) {
            throw new \Exception('密码错误');
        }
        $userInfo = new LoginUserInfo();
        $userInfo->username = $userPlatform->platform_id;
        $userInfo->scope = 'auth_base';
        $userInfo->platform = $userPlatform->platform;
        $userInfo->platform_user_id = $userPlatform->platform_id;
        $userInfo->user_platform = $userPlatform->platform;
        $userInfo->user_platform_user_id = $userPlatform->platform_id;
        return $userInfo;
    }

    private function mobileLogin()
    {
        $userPlatform = $this->getUser($this->mobile, UserInfo::PLATFORM_H5);
        if (!$userPlatform) {
            throw new \Exception('用户不存在');
        }
        $userInfo = new LoginUserInfo();
        $userInfo->username = $userPlatform->platform_id;
        $userInfo->scope = 'auth_base';
        $userInfo->platform = $userPlatform->platform;
        $userInfo->platform_user_id = $userPlatform->platform_id;
        $userInfo->user_platform = $userPlatform->platform;
        $userInfo->user_platform_user_id = $userPlatform->platform_id;
        return $userInfo;
    }

    /**
     * @param $mobile
     * @param $platform
     * @return array|\yii\db\ActiveRecord|null|UserPlatform
     */
    public function getUser($mobile, $platform)
    {
        return UserPlatform::findOne([
            'platform' => $platform, 'platform_id' => $mobile, 'mall_id' => \Yii::$app->mall->id
        ]);
    }
}
