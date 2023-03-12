<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\mall_member;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;

class SetPayPasswordForm extends Model
{
    public $pay_password;
    public $verify_pay_password;
    public $old_pay_password;

    public function rules()
    {
        return [
            [['pay_password' , 'verify_pay_password'], 'required'],
            [['pay_password', 'verify_pay_password', 'old_pay_password'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pay_password' => '支付密码',
            'verify_pay_password' => '确认支付密码',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->checkData();

            $user = $this->getUser();
            
            if (!$user) {
                throw new \Exception('会员不存在');
            }

            $userInfo = $user->userInfo;

            if ($userInfo->pay_password) {
                throw new \Exception('会员已设置过支付码，无需重复');
            }

            $userInfo->pay_password = \Yii::$app->getSecurity()->generatePasswordHash($this->verify_pay_password);
            $res = $userInfo->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($userInfo));
            }
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '设置成功',
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function getUser()
    {
        $user = User::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'id' => \Yii::$app->user->id,
            'is_delete' => 0
        ])
            ->with('userInfo')
            ->one();

        return $user;
    }

    private function checkData()
    {
        if ($this->pay_password != $this->verify_pay_password) {
            throw new \Exception('密码输入不一致');
        }

        if (strlen($this->verify_pay_password) != 6) {
            throw new \Exception('请输入6位数的支付密码');
        }
    }

    public function updatePayPassword()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->checkData();

            $user = $this->getUser();
            
            if (!$user) {
                throw new \Exception('会员不存在');
            }

            $userInfo = $user->userInfo;

            if (!\Yii::$app->getSecurity()->validatePassword($this->old_pay_password, $this->user->userInfo->pay_password)){
                throw new \Exception('原密码错误');
            }

            if ($this->old_pay_password == $this->verify_pay_password) {
                throw new \Exception('新密码与旧密码不能相同');
            }


            $userInfo->pay_password = \Yii::$app->getSecurity()->generatePasswordHash($this->verify_pay_password);
            $res = $userInfo->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($userInfo));
            }
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
