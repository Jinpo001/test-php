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

class CashierModifyForm extends Model
{
    public $id;
    public $number;
    public $name;
    public $mobile;
    public $username;
    public $store_id;
    public $status;

    public function rules()
    {
        return [
            [['id', 'number', 'name', 'mobile', 'username', 'store_id', 'status'], 'required'],
            [['number', 'name', 'mobile', 'username'], 'string'],
            [['id', 'status', 'store_id'], 'integer'],
            [['number', 'name', 'mobile', 'username'], 'trim']
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => '编号',
            'name' => '姓名',
            'mobile' => '电话',
            'username' => '账号',
            'store_id' => '门店',
            'status' => '启用状态',
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

            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'id' => $this->id,
                'is_delete' => 0
            ])->with('user')->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            if (!$cashier->user) {
                throw new \Exception('关联用户异常');
            }

            $cashier->number = $this->number;
            $cashier->store_id = $this->store_id;
            $cashier->status = $this->status;
            $cashier->save();

            $cashier->user->nickname = $this->name;
            $cashier->user->mobile = $this->mobile;
            $cashier->user->username = $this->username;
            $cashier->user->save();

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
            ['user_id' => $userIds],
        ])
            ->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->one();

        if ($cashier) {
            if ($cashier->number == $this->number && $cashier->id != $this->id) {
                throw new \Exception('编号已存在');
            }

            if ($cashier->user->username == $this->username && $cashier->id != $this->id) {
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
