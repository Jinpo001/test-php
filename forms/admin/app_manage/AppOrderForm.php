<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\app_manage;

use app\core\response\ApiCode;
use app\forms\admin\PaySettingForm;
use app\forms\admin\export\AdminCommonExport;
use app\forms\admin\export\AppOrderExport;
use app\models\AppManage;
use app\models\AppOrder;
use app\models\Model;

class AppOrderForm extends Model
{
    public $search;
    public $flag;
    public $fields;
    public $order_no;

    public function rules()
    {
        return [
            [['flag', 'order_no'], 'string'],
            [['search', 'fields'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $query = AppOrder::find()->andWhere(['is_delete' => 0, 'is_pay' => 1]);

            if (!\Yii::$app->role->isSuperAdmin) {
                $query->andWhere(['user_id' => \Yii::$app->user->id]);
            }

            $search = json_decode($this->search, true);
            if ($search['keyword_1'] && $search['keyword']) {
                switch ($search['keyword_1']) {
                    case 'order_no':
                        $query->andWhere(['like', 'order_no', $search['keyword']]);
                        break;
                    case 'nickname':
                        $query->andWhere(['like', 'nickname', $search['keyword']]);
                        break;
                    case 'app_name':
                        $query->andWhere(['like', 'app_name', $search['keyword']]);
                        break;
                    default:
                        # code...
                        break;
                }
            }

            if ($search['date_start'] && $search['date_end']) {
                $query->andWhere(['>=', 'created_at', $search['date_start']]);
                $query->andWhere(['<=', 'created_at', $search['date_end']]);
            }

            // if ($search['status'] != 0) {
            //     switch ($search['status']) {
            //         case '1':
            //             $query->andWhere(['is_pay' => 0]);
            //             break;
            //         case '2':
            //             $query->andWhere(['is_pay' => 1]);
            //             break;
                    
            //         default:
            //             # code...
            //             break;
            //     }
            // }

            if ($this->flag == "EXPORT") {
                $queueId = AdminCommonExport::handle([
                    'export_class' => 'app\\forms\\admin\\export\\AppOrderExport',
                    'params' => [
                        'query' => $query,
                        'fieldsKeyList' => $this->fields,
                    ],
                ]);

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'queue_id' => $queueId
                    ]
                ];
            }

            $list = $query->page($pagination, 10)->orderBy(['created_at' => SORT_DESC])->all();

            $newList = [];
            foreach ($list as $key => $value) {
                $newItem = [
                    'order_no' => $value->order_no,
                    'nickname' => $value->nickname,
                    'app_name' => $value->app_name,
                    'pay_price' => $value->pay_price . '元',
                    'status' => $value->is_pay ? '已完成' : '待付款',
                ];
                $newList[] = $newItem;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination,
                    'export_list' => (new AppOrderExport())->fieldsList()
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function queryOrder()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $order = AppOrder::find()->andWhere(['order_no' => $this->order_no])->one();

            $isPay = false;

            if ($order && $order->is_pay) {
                $isPay = true;
            }


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'is_pay' => $isPay
                ],
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
