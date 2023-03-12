<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\models\Store;
use app\models\User;
use app\plugins\teller\models\TellerCashier;

class CashierStoreForm extends Model
{
    public $number;
    public $name;
    public $mobile;
    public $username;
    public $password;
    public $password_verify;
    public $store_id;
    public $status;

    public function rules()
    {
        return [
            [['number', 'name', 'mobile', 'username', 'password', 'password_verify', 'store_id', 'status'], 'required'],
            [['number', 'name', 'mobile', 'username', 'password', 'password_verify'], 'string'],
            [['status', 'store_id'], 'integer'],
            [['number', 'name', 'mobile', 'username', 'password', 'password_verify'], 'trim']
        ];
    }

    public function attributeLabels()
    {
        return [
            'number' => '编号',
            'name' => '姓名',
            'mobile' => '电话',
            'username' => '账号',
            'store_id' => '门店',
            'status' => '启用状态',
            'password' => '密码',
            'password_verify' => '确认密码',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->checkData();

            $user = new User();
            $user->mall_id = \Yii::$app->mall->id;
            $user->mch_id = \Yii::$app->user->identity->mch_id;
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->nickname = $this->name;
            $user->username = $this->username;
            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $user->mobile = $this->mobile;
            $user->save();

            $cashier = new TellerCashier();
            $cashier->user_id = $user->id;
            $cashier->mall_id = \Yii::$app->mall->id;
            $cashier->mch_id = \Yii::$app->user->identity->mch_id;
            $cashier->creator_id = \Yii::$app->user->id;
            $cashier->number = $this->number;
            $cashier->store_id = $this->store_id;
            $cashier->status = $this->status;
            $cashier->save();

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];

        }catch(\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function checkData()
    {
        if (mb_strlen($this->username) < 6 || mb_strlen($this->username) > 16) {
            throw new \Exception('账号长度范围6至16个字符');
        }

        if (mb_strlen($this->password) < 6 || mb_strlen($this->password) > 16) {
            throw new \Exception('密码长度范围6至16个字符');
        }

        if ($this->password != $this->password_verify) {
            throw new \Exception('密码不一致');
        }

        if (mb_strlen($this->name) < 1 || mb_strlen($this->name) > 30) {
            throw new \Exception('姓名长度范围1至30个字符');
        }

        if (mb_strlen($this->number) < 1 || mb_strlen($this->number) > 30) {
            throw new \Exception('编号长度范围1至30个字符');
        }

        if (mb_strlen($this->mobile) > 11) {
            throw new \Exception('请输入正确的手机号');
        }

        if (!in_array($this->status, [0,1])) {
            throw new \Exception('status参数合法值[0,1]');
        }

        $userIds = User::find()->andWhere(['username' => $this->username])->select('id');
        $cashier = TellerCashier::find()->andWhere([
            'or',
            ['number' => $this->number],
            ['user_id' => $userIds]
        ])
            ->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->with('user')
            ->one();

        if ($cashier) {
            if ($cashier->number == $this->number) {
                throw new \Exception('编号已存在');
            }

            if ($cashier->user->username == $this->username) {
                throw new \Exception('账号已存在');
            }
        }

        $store = Store::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->store_id,
            'is_delete' => 0
        ])->one();

        if (!$store) {
            throw new \Exception('门店不存在');
        }
    }
}
