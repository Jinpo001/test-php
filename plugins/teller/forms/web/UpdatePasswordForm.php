<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;

class UpdatePasswordForm extends Model
{
    public $password_old;
    public $password;
    public $password_verify;

    public function rules()
    {
        return [
            [['password', 'password_verify', 'password_old'], 'required'],
            [['password_old', 'password', 'password_verify'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [
            'password_old' => '旧密码',
            'password' => '密码',
            'password_verify' => '密码'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $user = User::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => \Yii::$app->user->id,
            ])->one();

            if (!$user) {
                throw new \Exception('用户不存在');
            }

            if (mb_strlen($this->password) < 6 || mb_strlen($this->password) > 16) {
                throw new \Exception('密码长度范围6至16个字符');
            }

            if ($this->password != $this->password_verify) {
                throw new \Exception('密码不一致');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($this->password_old, $user->password)) {
                throw new \Exception('原密码不正确');
            }

            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $res = $user->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }
}
