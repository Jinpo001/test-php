<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;


use app\core\response\ApiCode;
use app\forms\common\prints\printer\FeiePrinter;
use app\forms\common\prints\printer\GpPrinter;
use app\forms\common\prints\printer\KdtPrinter;
use app\forms\common\prints\printer\YilianyunPrinter;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\Printer;
use app\plugins\teller\forms\common\TellerFirstTemplate;
use app\plugins\teller\forms\mall\export\ShiftsExport;
use app\plugins\teller\forms\mall\export\ShiftsGoodsExport;
use app\plugins\teller\forms\mall\export\ShiftsOrderExport;
use app\plugins\teller\forms\mall\export\ShiftsRefundOrderExport;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerPrinterSetting;
use app\plugins\teller\models\TellerWorkLog;

class ShiftsForm extends Model
{
    public $id;
    public $start_time;
    public $end_time;
    public $cashier_id;
    public $store_id;
    public $page;

    public $order_type;

    public $flag;
    public $fields;

    public $keyword;

    public $print_id;
    public $work_log_id;
    public $ids;

    public function rules()
    {
        return [
            [['start_time', 'end_time', 'flag', 'order_type', 'keyword'], 'string'],
            [['cashier_id', 'store_id', 'id', 'print_id', 'work_log_id', 'page'], 'integer'],
            [['fields', 'ids'], 'safe'],
        ];
    }

    public function getList()
    {
        try {
            $query = TellerWorkLog::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'status' => TellerWorkLog::FINISH
            ]);

            if ($this->cashier_id) {
                $query->andWhere(['cashier_id' => $this->cashier_id]);
            }

            if ($this->store_id) {
                $query->andWhere(['store_id' => $this->store_id]);
            }

            if ($this->start_time && $this->end_time) {
                $query->andWhere(['>=', 'start_time', $this->start_time]);
                $query->andWhere(['<=', 'end_time', $this->end_time]);
            }

            if ($this->flag == "EXPORT") {
                $queueId = CommonExport::handle([
                    'export_class' => 'app\\plugins\\teller\\forms\\mall\\export\\ShiftsExport',
                    'params' => [
                        'query' => $query,
                        'fieldsKeyList' => $this->fields,
                    ]
                ]);

                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '请求成功',
                    'data' => [
                        'queue_id' => $queueId
                    ]
                ];
            }

            $list = $query->with('cashier', 'store')
                ->orderBy(['id' => SORT_DESC])
                ->page($pagination, 20, $this->page)
                ->all();

            $list = array_map(function($item) {
                $extra = json_decode($item->extra_attributes, true);
                return [
                    'id' => $item->id,
                    'name' => $item->cashier->user->nickname,
                    'number' => $item->cashier->number,
                    'store_name' => $item->store->name,
                    'start_time' => $item->start_time,
                    'end_time' => $item->end_time,
                    'total_pay_money' => isset($extra['proceeds']['total_proceeds']) ? $extra['proceeds']['total_proceeds'] : 0,
                    'total_recharge_money' => isset($extra['recharge']['total_recharge']) ? $extra['recharge']['total_recharge'] : 0,
                    'refund_money' => isset($extra['refund']['total_refund']) ? $extra['refund']['total_refund'] : 0,
                ];
            }, $list);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
                    'export_list' => (new ShiftsExport())->fieldsList(),
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


    public function show()
    {
        try {
            $workLog = TellerWorkLog::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'status' => TellerWorkLog::FINISH,
                'id' => $this->id
            ])->with('cashier', 'store')->one();

            if (!$workLog) {
                throw new \Exception('班次不存在');
            }

            $extra = $workLog->extra_attributes ? json_decode($workLog->extra_attributes, true) : [];

            $detail = array_merge([
                'start_time' => $workLog->start_time,
                'end_time' => $workLog->end_time,
                'number' => $workLog->cashier->number,
                'name' => $workLog->cashier->user->nickname,
            ], $extra);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
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

    public function getGoods()
    {
        if ($this->flag == "EXPORT") {
            $queueId = CommonExport::handle([
                'export_class' => 'app\\plugins\\teller\\forms\\mall\\export\\ShiftsGoodsExport',
                'params' => [
                    'fieldsKeyList' => $this->fields,
                ],
                'model_class' => 'app\\plugins\\teller\\forms\\mall\\ShiftsForm',
                'function_name' => 'getGoodsQuery',
                'function_params' => ['mch_id' => \Yii::$app->user->identity->mch_id]
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'queue_id' => $queueId
                ]
            ];
        }

        $query = $this->getGoodsQuery(\Yii::$app->user->identity->mch_id);

        $list = $query->page($pagination, 5)->all();

        $newList = [];
        foreach ($list as $key => $value) {
            $goodsInfo = json_decode($value->goods_info, true);
            $attr = [];
            foreach ($goodsInfo['attr_list'] as $attrKey => $attrValue) {
                $attr[] = sprintf('%s:%s', $attrValue['attr_group_name'], $attrValue['attr_name']);
            }
            $newItem = [];
            $newItem['order_detail_id'] = $value->id;
            $newItem['goods_id'] = $value->goods->id;
            $newItem['name'] = $value->goods->goodsWarehouse->name;
            $newItem['cover_pic'] = $value->goods->goodsWarehouse->cover_pic;
            $newItem['attr'] = $attr;
            $newItem['goods_price'] = $value->goods->price;
            $newItem['num'] = $value->num;
            $newItem['total_price'] = $value->total_price;
            $newList[] = $newItem;
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new ShiftsGoodsExport())->fieldsList(),
            ]
        ];
    }

    /**
    *  该方法在导出队列中有使用 所以mallId mchId 需要传入
    */
    public function getGoodsQuery($mchId)
    {
        $mallId = \Yii::$app->mall->id;
        $workLog = TellerWorkLog::find()->andWhere([
            'mall_id' => $mallId,
            'mch_id' => $mchId,
            'is_delete' => 0,
            'status' => TellerWorkLog::FINISH,
            'id' => $this->id
        ])->with('cashier', 'store')->one();

        if (!$workLog) {
            throw new \Exception('班次不存在');
        }

        $orderIds = TellerOrders::find()->andWhere([
            'mall_id' => $mallId,
            'mch_id' => $mchId,
            'order_type' => TellerOrders::ORDER_TYPE_ORDER,
            'is_pay' => 1,
            'work_log_id' => $workLog->id
        ])->select('order_id');

        $query = OrderDetail::find()
            ->andWhere(['order_id' => $orderIds])
            ->select(['id', 'goods_id', 'num', 'total_price', 'goods_info'])
            ->with(['goods' => function($query) {
                $query->select(['id', 'goods_warehouse_id', 'price']);
            }, 'goods.goodsWarehouse' => function($query) {
                $query->select(['id', 'name', 'cover_pic']);
            }]);

        if ($this->keyword) {
            $goodsWarehouseIds = GoodsWarehouse::find()->andWhere([
                'mall_id' => $mallId,
                'is_delete' => 0
            ])
            ->andWhere(['like', 'name', $this->keyword])
            ->select('id');
            
            $goodsIds = Goods::find()->andWhere([
                'mall_id' => $mallId,
                'is_delete' => 0,
                'mch_id' => $mchId,
                'goods_warehouse_id' => $goodsWarehouseIds
            ])->select('id');

            $query->andWhere([
                'or',
                ['goods_id' => $goodsIds],
                ['like', 'goods_id', $this->keyword]
            ]);
        }

        if ($this->ids) {
            $idList = explode(',', $this->ids);
            $query->andWhere(['id' => $idList]);
        }

        return $query;
    }

    public function getOrders()
    {
        $workLog = TellerWorkLog::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'status' => TellerWorkLog::FINISH,
            'id' => $this->id
        ])->with('cashier', 'store')->one();

        if (!$workLog) {
            throw new \Exception('班次不存在');
        }

        $query = TellerOrders::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_pay' => 1,
            'work_log_id' => $workLog->id
        ]);

        if ($this->order_type) {
            $query->andWhere(['order_type' => $this->order_type]);
        }

        if ($this->ids && !is_array($this->ids)) {
            $idList = explode(',', $this->ids);
            $query->andWhere(['id' => $idList]);
        }

        if ($this->flag == "EXPORT") {
            $queueId = CommonExport::handle([
                'export_class' => 'app\\plugins\\teller\\forms\\mall\\export\\ShiftsOrderExport',
                'params' => [
                    'query' => $query,
                    'fieldsKeyList' => $this->fields
                ]
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'queue_id' => $queueId
                ]
            ];
        }

        $list = $query->with('order', 'reOrder')
            ->orderBy(['id' => SORT_DESC])
            ->page($pagination, 5)
            ->all();

        $newList = [];
        foreach ($list as $item) {
            $newList[] = [
                'teller_order_id' => $item->id,
                'created_at' => $item->created_at,
                'order_no' => $item->order ? $item->order->order_no : $item->reOrder->order_no,
                'order_id' => $item->order ? $item->order->id : $item->reOrder->id,
                'order_type' =>  TellerOrders::ORDER_TYPE_LIST[$item->order_type],
                'pay_type' => $item->getPayWay($item->pay_type),
                'total_pay_price' => $item->order ? $item->order->total_pay_price : $item->reOrder->pay_price
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new ShiftsOrderExport())->fieldsList(),
            ]
        ];
    }

    // 退款订单
    public function getRefundOrders()
    {
        $workLog = TellerWorkLog::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'status' => TellerWorkLog::FINISH,
            'id' => $this->id
        ])->with('cashier', 'store')->one();

        if (!$workLog) {
            throw new \Exception('班次不存在');
        }

        $orderIds = TellerOrders::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_pay' => 1,
            'work_log_id' => $workLog->id
        ])->select('order_id');

        $query = OrderRefund::find()->orderBy(['id' => SORT_DESC]);

        if ($this->ids) {
            $idList = explode(',', $this->ids);
            $query->andWhere(['id' => $idList]);
        } else {
            $query->andWhere(['order_id' => $orderIds]);
        }

        if ($this->flag == "EXPORT") {
            $queueId = CommonExport::handle([
                'export_class' => 'app\\plugins\\teller\\forms\\mall\\export\\ShiftsRefundOrderExport',
                'params' => [
                    'query' => $query,
                    'fieldsKeyList' => $this->fields,
                ]
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'queue_id' => $queueId
                ]
            ];
        }

        $list = $query->page($pagination, 5)->all();

        $newList = [];
        $tellerOrder = new TellerOrders();
        foreach ($list as $item) {
            $newList[] = [
                'refund_order_id' => $item->id,
                'created_at' => $item->created_at,
                'order_no' => $item->order_no,
                'refund_type' => $tellerOrder->getPayWay($item->order->paymentOrder->pay_type),
                'refund_price' => $item->reality_refund_price
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new ShiftsRefundOrderExport())->fieldsList(),
            ]
        ];
    }
}
