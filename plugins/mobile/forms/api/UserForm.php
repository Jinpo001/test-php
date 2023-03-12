<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/17
 * Time: 9:27 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\forms\api;

use app\forms\common\CommonOption;
use app\models\User;
use app\models\UserInfo;
use app\models\UserPlatform;
use app\plugins\mobile\forms\common\CommonSetting;
use app\plugins\mobile\forms\Model;
use app\validators\ValidateCodeValidator;
use yii\helpers\Json;

class UserForm extends Model
{
    public $avatar;

    public $nickname;

    public $password;

    public $validate_code_id;
    public $sms_captcha;
    public $mobile;

    public $key;

    public $old_password;
    public $new_password;
    public $again_password;

    private const VALIDATE_KEY_LIST = 'validate_key_list';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['u_avatar'] = ['avatar'];
        $scenarios['u_nickname'] = ['nickname'];
        $scenarios['u_password'] = ['old_password', 'new_password', 'again_password'];
        $scenarios['u_mobile'] = ['mobile', 'sms_captcha', 'validate_code_id', 'key'];
        $scenarios['validate_by_password'] = ['password'];
        $scenarios['validate_by_mobile'] = ['mobile', 'sms_captcha', 'validate_code_id'];
        $scenarios['u_mobile_password'] = ['mobile', 'sms_captcha', 'validate_code_id', 'new_password', 'again_password'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            ['avatar', 'required', 'on' => 'u_avatar'],
            ['nickname', 'required', 'on' => 'u_nickname'],
            ['password', 'required', 'on' => 'validate_by_password'],
            [['mobile', 'sms_captcha', 'validate_code_id'], 'required',
                'on' => ['validate_by_mobile', 'u_mobile', 'u_mobile_password']],
            [['key'], 'required', 'on' => 'u_mobile'],
            [['sms_captcha'], ValidateCodeValidator::class,
                'mobileAttribute' => 'mobile',
                'validateCodeIdAttribute' => 'validate_code_id',
                'on' => ['validate_by_mobile', 'u_mobile']
            ],
            [['old_password'], 'required', 'on' => 'u_password'],
            [['new_password', 'again_password'], 'required', 'on' => ['u_password', 'u_mobile_password']],
            [['avatar', 'nickname', 'key', 'old_password', 'new_password', 'again_password'], 'trim'],
            [['avatar', 'nickname', 'key', 'old_password', 'new_password', 'again_password'], 'string'],
            [['old_password', 'new_password', 'again_password'], 'string', 'min' => 6, 'max' => 15],
            ['nickname', 'string', 'min' => 1, 'max' => 16]
        ];
    }

    public function attributeLabels()
    {
        return [
            'avatar' => '头像',
            'nickname' => '昵称',
            'mobile' => '手机号',
            'sms_captcha' => '手机验证码',
            'validate_code_id' => '验证id',
            'password' => '验证密码',
            'key' => '身份验证',
            'old_password' => '旧密码',
            'new_password' => '新密码',
            'again_password' => '新密码',
        ];
    }

    /**
     * @return array
     * 修改头像
     */
    public function avatar()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $user->userInfo->avatar = $this->avatar;
        if (!$user->userInfo->save()) {
            return $this->getErrorResponse($user->userInfo);
        }
        return $this->success(['msg' => '设置成功']);
    }

    /**
     * @return array
     * 修改昵称
     */
    public function nickname()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $user->nickname = $this->nickname;
        if (!$user->save()) {
            return $this->getErrorResponse($user);
        }
        return $this->success(['msg' => '设置成功']);
    }

    /**
     * 修改密码
     * 只有h5登录才能修改密码
     */
    public function password()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $userPlatForm = UserPlatform::findOne([
            'platform' => UserInfo::PLATFORM_H5,
            'user_id' => $user->id
        ]);
        if ($this->scenario == 'u_password') {
            if (!\Yii::$app->getSecurity()->validatePassword($this->old_password, $userPlatForm->password)) {
                return $this->fail(['msg' => '旧密码错误，请确认后输入']);
            }
        }
        if ($this->scenario == 'u_mobile_password') {
            if ($userPlatForm->platform_id !== $this->mobile) {
                return $this->fail(['msg' => '手机号错误']);
            }
        }
        $newPassword = \Yii::$app->security->generatePasswordHash($this->new_password);
        if (!\Yii::$app->security->validatePassword($this->again_password, $newPassword)) {
            return $this->fail(['msg' => '两次新密码输入不一致，请确认后输入']);
        }
        $userPlatForm->password = $newPassword;
        if (!$userPlatForm->save()) {
            return $this->fail(['msg' => $this->getErrorMsg($userPlatForm)]);
        }
        return $this->success(['msg' => '设置成功']);
    }

    /**
     * @return array
     * 修改手机号
     */
    public function mobile()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        // 验证身份验证信息是否有效
        $validateKey = $this->getValidateKey();
        $key = $this->getKey();
        if (!(isset($validateKey[$key]) && $validateKey[$key] == $this->key && \Yii::$app->cache->get($this->key))) {
            // 身份验证信息获取失败时，需要重新验证身份
            return $this->fail(['msg' => '身份验证错误或超时，请重新验证', 'retry' => 1]);
        }

        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $user->mobile = $this->mobile;
        if (!$user->save()) {
            return $this->getErrorResponse($user);
        }

        // h5修改手机号时，将用户平台信息中的手机也修改掉
        $userPlatForm = UserPlatform::findOne([
            'platform' => UserInfo::PLATFORM_H5,
            'user_id' => $user->id
        ]);
        if (!$userPlatForm) {
            $userPlatForm->platform_id = $this->mobile;
            if (!$userPlatForm->save()) {
                return $this->getErrorResponse($userPlatForm);
            }
        }

        // 修改完手机号，删除身份验证信息，防止重复利用
        \Yii::$app->cache->delete($this->key);
        unset($validateKey[$key]);
        $this->setValidateKey($validateKey);

        return $this->success(['msg' => '设置成功']);
    }

    /**
     * @return array
     * 验证身份 可通过密码验证和手机号验证码验证
     */
    public function validateIdentity()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->scenario == 'validate_by_password') {
            /** @var User $user */
            $user = \Yii::$app->user->identity;

            if (!\Yii::$app->getSecurity()->validatePassword($this->password, $user->password)) {
                return $this->fail(['msg' => '密码错误']);
            }
        }
        if ($this->scenario == 'validate_by_mobile') {
            /** @var User $user */
            $user = \Yii::$app->user->identity;

            if ($this->mobile != $user->mobile) {
                return $this->fail(['msg' => '手机号错误']);
            }
        }
        return $this->success(['msg' => '验证成功', 'key' => $this->getString()]);
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     * 通过身份验证后，生成身份验证信息字符串
     */
    private function getString()
    {
        $string = \Yii::$app->security->generateRandomString(6);
        // 身份验证信息有效期10分钟
        \Yii::$app->cache->set($string, true, 10 * 60);
        $res = $this->getValidateKey();
        $key = $this->getKey();
        $res[$key] = $string;
        $this->setValidateKey($res);
        return $string;
    }

    /**
     * @return string
     * 获取身份验证信息存储的key
     */
    private function getKey()
    {
        return 'identity_' . \Yii::$app->user->identity->mobile;
    }

    /**
     * @return array|mixed|null
     * 获取身份验证缓存列表
     */
    private function getValidateKey()
    {
        $res = \Yii::$app->cache->get(self::VALIDATE_KEY_LIST);
        if (!$res) {
            $res = [];
        } else {
            $res = Json::decode($res, true);
        }
        return $res;
    }

    /**
     * @param array $list
     * 设置身份验证缓存
     */
    private function setValidateKey($list = [])
    {
        \Yii::$app->cache->set(self::VALIDATE_KEY_LIST, Json::encode($list, JSON_UNESCAPED_UNICODE));
    }

    public function contact()
    {
        $list = CommonOption::get(CommonSetting::H5_CONTACT, \Yii::$app->mall->id, 'plugin');
        $pic = '';
        if (!empty($list)) {
            $pic = $list[rand(0, count($list) - 1)];
        }
        return $this->success(['pic' => $pic]);
    }
}
