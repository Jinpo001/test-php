<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\User;
use app\plugins\teller\models\TellerCashier;

class CashierForm extends Model
{
    public $id;
    public $status;
    public $keyword;
    public $password;
    public $store_id;

    public function rules()
    {
        return [
            [['id', 'status', 'store_id'], 'integer'],
            [['keyword', 'password'], 'string'],
            [['keyword', 'password'], 'trim'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $query = TellerCashier::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {

            $userIds = User::find()->andWhere([
                'or',
                ['like', 'nickname', $this->keyword],
                ['like', 'mobile', $this->keyword],
            ])->select('id');

            $query->andWhere([
                'or',
                ['like', 'number', $this->keyword],
                ['user_id' => $userIds]
            ]);
        }

        if ($this->store_id) {
            $query->andWhere(['store_id' =>  $this->store_id]);
        }

        $cashiers = $query->with('store', 'creator', 'user')->orderBy(['id' => SORT_DESC])->page($pagination)->all();

        $list = array_map(function($cashier) {
            return [
                'id' => $cashier->id,
                'number' => $cashier->number,
                'name' => $cashier->user->nickname,
                'mobile' => $cashier->user->mobile,
                'username' => $cashier->user->username,
                'status' => $cashier->status,
                'store_name' => $cashier->store->name,
                'creator_name' => $cashier->creator->nickname,
                'created_at' => date('Y-m-d', strtotime($cashier->created_at)),
                'sale_money' => $cashier->sale_money,
                'push_money' => $cashier->push_money
            ];
        }, $cashiers);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'pagination' => $pagination,
                'list' => $list,
            ],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->with('store', 'user')->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            $cashier = [
                'id' => $cashier->id,
                'number' => $cashier->number,
                'name' => $cashier->user->nickname,
                'mobile' => $cashier->user->mobile,
                'username' => $cashier->user->username,
                'status' => $cashier->status,
                'store_id' => $cashier->store->id,
                'store_name' => $cashier->store->name
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'cashier' => $cashier,
                ]
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            $cashier->is_delete = 1;
            $res = $cashier->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($cashier));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function updateStatus()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->status = $this->status ? 1 : 0;
            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            $cashier->status = $this->status;
            $res = $cashier->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($cashier));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function updatePassword()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->with('user')->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            if (!$cashier->user) {
                throw new \Exception('收银员用户异常');
            }

            if (mb_strlen($this->password) < 6 || mb_strlen($this->password) > 16) {
                throw new \Exception('密码长度范围6至16个字符');
            }

            $cashier->user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);;
            $res = $cashier->user->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($cashier->user));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
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
