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

class VerifyPayPasswordForm extends Model
{
    public $pay_password;

    public function rules()
    {
        return [
            [['pay_password'], 'required'],
            [['pay_password'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pay_password' => '支付密码',
        ];
    }

    public function verify()
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

            if (!$userInfo->pay_password) {
                throw new \Exception('用户未设置支付密码');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($this->pay_password, $userInfo->pay_password)){
                throw new \Exception('支付密码错误');
            }
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '验证通过',
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
        if (strlen($this->pay_password) != 6) {
            throw new \Exception('请输入6位数的支付密码');
        }
    }
}
