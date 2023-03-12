<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\teller\models\TellerSales;

class SalesForm extends Model
{
    public $id;
    public $status;
    public $keyword;
    public $store_id;

    public function rules()
    {
        return [
            [['id', 'status', 'store_id'], 'integer'],
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $query = TellerSales::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'number', $this->keyword],
                ['like', 'name', $this->keyword],
                ['like', 'mobile', $this->keyword],
            ]);
        }

        if ($this->store_id) {
            $query->andWhere(['store_id' =>  $this->store_id]);
        }

        $salesList = $query->with('store', 'creator')->orderBy(['id' => SORT_DESC])->page($pagination)->all();

        $list = array_map(function($sales) {
            return [
                'id' => $sales->id,
                'number' => $sales->number,
                'name' => $sales->name,
                'mobile' => $sales->mobile,
                'status' => $sales->status,
                'store_name' => $sales->store->name,
                'creator_name' => $sales->creator->nickname,
                'created_at' => date('Y-m-d', strtotime($sales->created_at)),
                'sale_money' => $sales->sale_money,
                'push_money' => $sales->push_money,
                'head_url' => $sales->head_url
            ];
        }, $salesList);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $sales = TellerSales::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->with('store')->one();

            if (!$sales) {
                throw new \Exception('导购员不存在');
            }

            $sales = [
                'id' => $sales->id,
                'number' => $sales->number,
                'name' => $sales->name,
                'mobile' => $sales->mobile,
                'status' => $sales->status,
                'store_id' => $sales->store->id,
                'store_name' => $sales->store->name,
                'head_url' => $sales->head_url
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'sales' => $sales,
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
            $sales = TellerSales::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$sales) {
                throw new \Exception('导购员不存在');
            }

            $sales->is_delete = 1;
            $sales->save();

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
            $sales = TellerSales::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$sales) {
                throw new \Exception('导购员不存在');
            }

            $sales->status = $this->status;
            $sales->save();

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
