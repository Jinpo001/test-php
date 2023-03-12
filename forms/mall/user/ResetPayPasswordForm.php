<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\user;

use app\core\response\ApiCode;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\models\Model;
use app\models\User;

class ResetPayPasswordForm extends Model
{
    public $user_id;

    public function rules()
    {
        return [
            [['user_id' ], 'required'],
            [['user_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => '用户ID',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {

            $user = $this->getUser();
            
            if (!$user) {
                throw new \Exception('会员不存在');
            }

            $userInfo = $user->userInfo;
            $userInfo->pay_password = '';
            $res = $userInfo->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($userInfo));
            }

            $this->sendSmsToUser($user);
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '重置成功',
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function sendSmsToUser($user)
    {
        try {
            \Yii::warning('----重置余额支付密码消息发送提醒----');
            if (!$user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => []
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = 'pay_password_reset';
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
    }

    public function getUser()
    {
        $user = User::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->user_id,
            'is_delete' => 0
        ])
            ->with('userInfo')
            ->one();

        return $user;
    }
}
