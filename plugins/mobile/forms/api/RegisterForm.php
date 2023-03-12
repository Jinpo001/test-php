<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/14
 * Time: 4:11 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\forms\api;

use app\core\newsms\Sms;
use app\core\response\ApiCode;
use app\events\UserEvent;
use app\forms\common\CommonAppConfig;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\models\CoreValidateCode;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;
use app\models\UserPlatform;
use app\plugins\mobile\forms\Model;
use app\plugins\mobile\models\LoginForm;
use app\validators\ValidateCodeValidator;
use Overtrue\EasySms\Message;

class RegisterForm extends Model
{
    public $mobile;
    public $sms_captcha;
    public $pic_captcha;
    public $validate_code_id;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['register'] = ['sms_captcha', 'validate_code_id', 'mobile'];
        $scenarios['send_sms_captcha'] = ['pic_captcha', 'mobile'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['sms_captcha'], 'required', 'on' => ['register']],
            [['validate_code_id'], 'required', 'on' => ['register'], 'message' => '请先发送验证码'],
            [['pic_captcha'], 'required', 'on' => ['send_sms_captcha']],
            [['sms_captcha'], ValidateCodeValidator::class,
                'mobileAttribute' => 'mobile',
                'validateCodeIdAttribute' => 'validate_code_id',
                'on' => ['register']
            ],
            [['pic_captcha'], 'captcha', 'captchaAction' => 'site/pic-captcha', 'on' => ['send_sms_captcha']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'pic_captcha' => '图形验证码',
            'sms_captcha' => '手机验证码',
        ];
    }

    public function register()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->validate()) {
                throw new \Exception($this->getErrorMsg());
            }
            $loginModel = new LoginForm();
            $user = $loginModel->getUser($this->mobile, UserInfo::PLATFORM_H5);
            if ($user) {
                throw new \Exception('该手机号已经注册，请直接登录');
            }

            $password = \Yii::$app->security->generateRandomString(6);
            $user = new User();
            $user->mall_id = \Yii::$app->mall->id;
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->username = $this->mobile;
            $user->nickname = $this->mobile;
            $user->unionid = '';
            $user->mobile = $this->mobile;
            $user->password = \Yii::$app->security
                ->generatePasswordHash(\Yii::$app->security->generateRandomString(), 5);

            if (!$user->save()) {
                throw new \Exception($this->getErrorMsg($user));
            }

            $uInfo = new UserInfo();
            $uInfo->user_id = $user->id;
            $uInfo->avatar = \Yii::$app->request->hostInfo .
                \Yii::$app->request->baseUrl .
                '/statics/img/app/user-default-avatar.png';
            $uInfo->platform_user_id = $this->mobile;
            $uInfo->platform = UserInfo::PLATFORM_H5;
            $uInfo->is_delete = 0;
            if (!$uInfo->save()) {
                throw new \Exception($this->getErrorMsg($uInfo));
            }
            $userIdentity = new UserIdentity();
            $userIdentity->user_id = $user->id;
            if (!$userIdentity->save()) {
                throw new \Exception($this->getErrorMsg($userIdentity));
            }
            $userPlatform = new UserPlatform();
            $userPlatform->mall_id = $user->mall_id;
            $userPlatform->user_id = $user->id;
            $userPlatform->platform = UserInfo::PLATFORM_H5;
            $userPlatform->platform_id = $this->mobile;
            $userPlatform->unionid = '';
            $userPlatform->password = \Yii::$app->security->generatePasswordHash($password, 5);
            if (!$userPlatform->save()) {
                $t->rollBack();
                throw new \Exception($this->getErrorMsg($userPlatform));
            }
            $t->commit();
            $this->sendSmsToUser($user, $password);

            $event = new UserEvent();
            $event->sender = $this;
            $event->user = $user;
            \Yii::$app->trigger(User::EVENT_REGISTER, $event);
            return $this->success(['msg' => '注册成功', 'access_token' => $user->access_token]);
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function sendSmsCaptcha()
    {
        try {
            if (!$this->validate()) {
                throw new \Exception($this->getErrorMsg());
            }
            $code = '' . rand(100000, 999999);
            $smsConfig = CommonAppConfig::getSmsConfig();
            if (!$smsConfig
                || empty($smsConfig['status'])
                || $smsConfig['status'] == 0
                || empty($smsConfig['captcha']['template_id'])) {
                throw new \Exception('短信信息尚未配置');
            }
            $coreValidateCode = new CoreValidateCode();
            $coreValidateCode->target = $this->mobile;
            $coreValidateCode->code = $code;
            if (!$coreValidateCode->save()) {
                throw new \Exception($this->getErrorMsg($coreValidateCode));
            }
            \Yii::$app->sms->module(Sms::MODULE_MALL)->send($this->mobile, new Message([
                'content' => null,
                'template' => $smsConfig['captcha']['template_id'],
                'data' => [
                    $smsConfig['captcha']['template_variable'] => $code,
                ],
            ]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '短信验证码已发送。',
                'data' => [
                    'validate_code_id' => $coreValidateCode->id,
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
            ];
        }
    }

    public function sendSmsToUser($user, $password)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => [$password]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = 'password';
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
    }
}
