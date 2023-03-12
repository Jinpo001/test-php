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

class ShiftsRefundOrderExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'created_at',
                'value' => '退款时间',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'refund_type',
                'value' => '退款方式',
            ],
            [
                'key' => 'refund_price',
                'value' => '退款金额',
            ],
        ];

        return $fieldsList;
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query->with(['order.paymentOrder'])->orderBy(['id' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '交班售后订单列表';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 0;
        $tellerOrder = new TellerOrders();
        foreach ($list as $key => $item) {
            $number++;
            $newItem = [];
            $newItem['number'] = $number;
            $newItem['created_at'] = $item->created_at;
            $newItem['order_no'] = $item->order_no;
            $newItem['refund_type'] = $tellerOrder->getPayWay($item->order->paymentOrder->pay_type); 
            $newItem['refund_price'] = (float)$item->reality_refund_price;
            $newList[] = $newItem;
        }
        
        $this->dataList = $newList;
    }
}
