<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;

use app\core\payment\Payment;
use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\models\Model;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\RechargeOrders;
use app\models\User;
use app\plugins\teller\Plugin;
use app\plugins\teller\forms\mall\export\PushOrderExport;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerPushOrder;
use app\plugins\teller\models\TellerSales;

class PushOrderForm extends Model
{
    public $user_type;
    public $store_id;
    public $start_date;
    public $end_date;
    public $pay_type;
    public $order_type;
    public $keyword_name;
    public $keyword_value;
    public $page;

    public $flag;
    public $fields;

    const ORDER = 'order';
    const RECHARGE = 'recharge';

    public function rules()
    {
        return [
            [['user_type'], 'required'],
            [['user_type', 'start_date', 'end_date', 'pay_type', 'order_type', 'keyword_name', 'keyword_value', 'flag'], 'string'],
            [['store_id', 'page'], 'integer'],
            [['fields'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_type' => '用户类型'
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $this->checkData();

            $query = TellerPushOrder::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
                'user_type' => $this->user_type
            ]);

            // 所属门店
            if ($this->store_id) {
                if ($this->user_type == TellerCashier::USER_TYPE) {
                    $cashierIds = TellerCashier::find()->andWhere([
                        'mall_id' => \Yii::$app->mall->id,
                        'store_id' => $this->store_id
                    ])->select('id');
                    $query->andWhere(['cashier_id' => $cashierIds]);
                } else {
                    $salesIds = TellerSales::find()->andWhere([
                        'mall_id' => \Yii::$app->mall->id, 
                        'store_id' => $this->store_id
                    ])->select('id');
                    $query->andWhere(['sales_id' => $salesIds]);
                }                
            }

            if ($this->start_date && $this->end_date) {
                $query->andWhere(['>=', 'created_at', $this->start_date]);
                $query->andWhere(['<=', 'created_at', $this->end_date]);
            }

            // 支付方式
            if ($this->pay_type) {
                $payTypeList = [
                    Payment::PAY_TYPE_WECHAT_SCAN,
                    Payment::PAY_TYPE_ALIPAY_SCAN,
                    Payment::PAY_TYPE_POS,
                    Payment::PAY_TYPE_CASH,
                    Payment::PAY_TYPE_BALANCE,
                ];
                if (!in_array($this->pay_type, $payTypeList)) {
                    throw new \Exception(sprintf('pay_type 参数支持 %s', json_encode($payTypeList)));
                }

                $valueList = [
                    Payment::PAY_TYPE_WECHAT_SCAN => 11,
                    Payment::PAY_TYPE_ALIPAY_SCAN => 12,
                    Payment::PAY_TYPE_POS => 10,
                    Payment::PAY_TYPE_CASH => 9,
                    Payment::PAY_TYPE_BALANCE => 3,
                ];

                $tellerOrderIds = TellerOrders::find()->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'pay_type' => $valueList[$this->pay_type],
                    'is_pay' => 1,
                ])->select('id');

                $query->andWhere(['teller_order_id' => $tellerOrderIds]);
            }

            if ($this->keyword_value) {
                switch ($this->keyword_name) {
                    case 'mobile':
                        if ($this->user_type == TellerCashier::USER_TYPE) {
                            $userIds = User::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id
                            ])
                                ->andWhere(['like', 'mobile', $this->keyword_value])
                                ->select('id');

                            $cashierIds = TellerCashier::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                                'user_id' => $userIds
                            ])->select('id');
                            $query->andWhere(['cashier_id' => $cashierIds]);
                        } else {
                            $salesIds = TellerSales::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                            ])
                                ->andWhere(['like', 'mobile', $this->keyword_value])
                                ->select('id');
                            $query->andWhere(['sales_id' => $salesIds]);
                        }
                        break;

                    case 'name':
                        if ($this->user_type == TellerCashier::USER_TYPE) {
                            $userIds = User::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id
                            ])
                                ->andWhere(['like', 'nickname', $this->keyword_value])
                                ->select('id');

                            $cashierIds = TellerCashier::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                                'user_id' => $userIds
                            ])->select('id');
                            $query->andWhere(['cashier_id' => $cashierIds]);
                        } else {
                            $salesIds = TellerSales::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                            ])
                                ->andWhere(['like', 'name', $this->keyword_value])
                                ->select('id');
                            $query->andWhere(['sales_id' => $salesIds]);
                        }
                        break;

                    case 'number':
                        if ($this->user_type == TellerCashier::USER_TYPE) {
                            $cashierIds = TellerCashier::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                            ])
                                ->andWhere(['like', 'number', $this->keyword_value])
                                ->select('id');
                            $query->andWhere(['cashier_id' => $cashierIds]);
                        } else {
                            $salesIds = TellerSales::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                            ])
                                ->andWhere(['like', 'number', $this->keyword_value])
                                ->select('id');
                            $query->andWhere(['sales_id' => $salesIds]);
                        }
                        break;

                    case 'order_no':
                        $orderIds = Order::find()->andWhere([
                            'mall_id' => \Yii::$app->mall->id,
                        ])
                            ->andWhere(['like', 'order_no', $this->keyword_value])
                            ->select('id');

                        $reOrderIds = RechargeOrders::find()->andWhere([
                            'mall_id' => \Yii::$app->mall->id,
                        ])
                            ->andWhere(['like', 'order_no', $this->keyword_value])
                            ->select('id');

                        $query->andWhere([
                            'or',
                            ['order_id' => $orderIds],
                            ['re_order_id' => $reOrderIds]
                        ]);
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }

            if ($this->order_type) {
                $query->andWhere(['order_type' => $this->order_type]);
            }

            if ($this->flag == "EXPORT") {
                $queueId = CommonExport::handle([
                    'export_class' => 'app\\plugins\\teller\\forms\\mall\\export\\PushOrderExport',
                    'params' => [
                        'query' => $query,
                        'fieldsKeyList' => $this->fields,
                        'user_type' => $this->user_type,
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

            if ($this->user_type == TellerCashier::USER_TYPE) {
                $query->with('cashier.user');
            } else {
                $query->with('sales');
            }

            $pushOrders = $query->with('order', 'reOrder', 'tellerOrder')
                ->orderBy(['id' => SORT_DESC])
                ->page($pagination, 20, $this->page)
                ->all();

            $list = array_map(function($pushOrder) {
                $data = [
                    'id' => $pushOrder->id,
                    'status' => $pushOrder->status == 'pending' ? '未完成' : '已完成',
                    'order_type' => $pushOrder->order_type == 'order' ? '买单' : '会员充值',
                    'refund_money' => $pushOrder->tellerOrder->refund_money,
                    'push_money' => $pushOrder->push_money,
                ];

                if ($pushOrder->order) {
                    $data['created_at'] = $pushOrder->order->created_at;
                    $data['order_no'] = $pushOrder->order->order_no;
                    $data['pay_type'] = (new TellerOrders)->getPayWay($pushOrder->tellerOrder->pay_type);
                    $data['total_pay_price'] = $pushOrder->order->total_pay_price;
                    $data['order_id'] = $pushOrder->order->id;
                } else {
                    $data['created_at'] = $pushOrder->reOrder->created_at;
                    $data['order_no'] = $pushOrder->reOrder->order_no;
                    $data['pay_type'] = (new TellerOrders)->getPayWay($pushOrder->tellerOrder->pay_type);
                    $data['total_pay_price'] = $pushOrder->reOrder->pay_price;
                    $data['order_id'] = $pushOrder->reOrder->id;
                }

                if ($pushOrder->user_type == TellerCashier::USER_TYPE) {
                    $data['number'] = $pushOrder->cashier->number;
                    $data['name'] = $pushOrder->cashier->user->nickname;
                } else {
                    $data['number'] = $pushOrder->sales->number;
                    $data['name'] = $pushOrder->sales->name;
                }

                return $data;
            }, $pushOrders);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'export_list' => $this->getFieldsList(),
                    'pagination' => $pagination,
                ],
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function getFieldsList()
    {
        return (new PushOrderExport())->fieldsList();
    }


    private function checkData()
    {
        if ($this->user_type && !in_array($this->user_type, [TellerCashier::USER_TYPE, TellerSales::USER_TYPE])) {
            throw new \Exception(sprintf('user_type 可选参数 %s|%s', TellerCashier::USER_TYPE, TellerSales::USER_TYPE));
        }

        if ($this->order_type && !in_array($this->order_type, [TellerPushOrder::ORDER_TYPE_ORDER, TellerPushOrder::ORDER_TYPE_RECHARGE])) {
            throw new \Exception(sprintf('order_type 可选参数 %s|%s', TellerPushOrder::ORDER_TYPE_ORDER, TellerPushOrder::ORDER_TYPE_RECHARGE));
        }
    }
}
