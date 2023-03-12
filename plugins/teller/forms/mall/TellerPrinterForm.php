<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\teller\models\TellerPrinterSetting;

class TellerPrinterForm extends Model
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

        $query = TellerPrinterSetting::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
        ]);

        if ($this->store_id) {
            $query->andWhere(['store_id' =>  $this->store_id]);
        }

        $list = $query->with('store', 'printer')->all();

        $list = array_map(function($item) {
            return [
                'id' => $item->id,
                'printer_name' => $item->printer ? $item->printer->name : '',
                'store_name' => $item->store ? $item->store->name : '',
                'status' => $item->status,
                'printer_id' => $item->printer_id,
                'store_id' => $item->store_id,
                'big' => $item->big,
                'show_type' => json_decode($item->show_type, true),
            ];
        }, $list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
            ],
        ];
    }

    public function getOptions()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $query = TellerPrinterSetting::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'status' => 1,
        ]);

        $list = $query->with('store', 'printer')->all();

        $list = array_map(function($item) {
            return [
                'id' => $item->id,
                'printer_name' => $item->printer ? $item->printer->name : '',
                'store_name' => $item->store ? $item->store->name : '',
                'status' => $item->status,
                'printer_id' => $item->printer_id,
                'store_id' => $item->store_id,
                'big' => $item->big,
                'show_type' => json_decode($item->show_type, true),
            ];
        }, $list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
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
            $detail = TellerPrinterSetting::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$detail) {
                throw new \Exception('打印设置不存在');
            }

            $detail = [
                'id' => $detail->id,
                'printer_id' => $detail->printer_id,
                'store_id' => $detail->store_id,
                'big' => $detail->big,
                'status' => $detail->status,
                'show_type' => json_decode($detail->show_type, true),
            ];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail,
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
            $printer = TellerPrinterSetting::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$printer) {
                throw new \Exception('打印设置不存在');
            }

            $printer->is_delete = 1;
            $printer->save();

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
            $printer = TellerPrinterSetting::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'id' => $this->id,
            ])->one();

            if (!$printer) {
                throw new \Exception('打印设置不存在');
            }

            $printer->status = $this->status;
            $printer->save();

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
