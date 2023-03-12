<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall\export;

use app\core\CsvExport;
use app\core\response\ApiCode;
use app\forms\mall\export\BaseExport;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerPushOrder;

class PushOrderExport extends BaseExport
{
    public $user_type;

    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ],
            [
                'key' => 'user_number',
                'value' => '编号',
            ],
            [
                'key' => 'name',
                'value' => '姓名',
            ],
            [
                'key' => 'order_type',
                'value' => '订单类型',
            ],
            [
                'key' => 'pay_type',
                'value' => '支付方式',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '实收金额',
            ],
            [
                'key' => 'refund_money',
                'value' => '退款金额',
            ],
            [
                'key' => 'push_money',
                'value' => '提成',
            ],
            [
                'key' => 'store_name',
                'value' => '所属门店',
            ],
            [
                'key' => 'mobile',
                'value' => '电话',
            ],
        ];

        return $fieldsList;
    }

    public function export($query = null)
    {
        $query = $this->query;
        if ($this->user_type == TellerCashier::USER_TYPE) {
            $query->with('cashier.user', 'cashier.store');
        } else {
            $query->with('sales.store');
        }

        $query = $query->with('order', 'tellerOrder')->orderBy(['id' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        $userType = $this->user_type == TellerCashier::USER_TYPE ? '收银员' : '导购员';
        $fileName = sprintf('%s业绩明细', $userType);

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 0;
        $orderStatus = TellerPushOrder::ORDER_STATUS_PENDING;
        $userType = TellerCashier::USER_TYPE;
        $orderType = TellerPushOrder::ORDER_TYPE_ORDER;
        $teOrderModel = new TellerOrders();
        foreach ($list as $key => $item) {
            $number++;
            $newItem = [];
            $newItem['number'] = $number;
            $newItem['status'] = $item->status == $orderStatus ? '未完成' : '已完成';
            $newItem['order_type'] = $item->order_type == $orderType ? '买单' : '会员充值';
            $newItem['pay_type'] = $teOrderModel->getPayWay($item->tellerOrder->pay_type);
            $newItem['refund_money'] = (float)$item->tellerOrder->refund_money;
            $newItem['push_money'] = (float)$item->push_money;

            if ($this->user_type == $userType) {
                $newItem['user_number'] = $item->cashier->number;
                $newItem['name'] = $item->cashier->user->nickname;
                $newItem['mobile'] = $item->cashier->user->mobile;
                $newItem['store_name'] = $item->cashier->store->name;
            } else {
                $newItem['user_number'] = $item->sales->number;
                $newItem['name'] = $item->sales->name;
                $newItem['mobile'] = $item->sales->mobile;
                $newItem['store_name'] = $item->sales->store->name;
            }

            if ($item->order) {
                $newItem['created_at'] = $item->order->created_at;
                $newItem['order_no'] = $item->order->order_no;
                $newItem['total_pay_price'] = (float)$item->order->total_pay_price;
            } else {
                $newItem['created_at'] = $item->reOrder->created_at;
                $newItem['order_no'] = $item->reOrder->order_no;
                $newItem['total_pay_price'] = (float)$item->reOrder->pay_price;
            }

            $newList[] = $newItem;
        }
        
        $this->dataList = $newList;
    }
}
