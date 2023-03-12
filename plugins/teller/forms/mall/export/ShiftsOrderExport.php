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

class ShiftsOrderExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'created_at',
                'value' => '操作时间',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'order_type',
                'value' => '订单类型',
            ],
            [
                'key' => 'pay_type',
                'value' => '付款方式',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '付款金额',
            ],
        ];

        return $fieldsList;
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query->with(['order' => function($query) {
            $query->select(['id','order_no', 'created_at', 'total_pay_price']);
        }, 'reOrder'])->orderBy(['id' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '交班订单列表';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 0;
        foreach ($list as $key => $item) {
            $number++;
            $newItem = [];
            $newItem['number'] = $number;
            $newItem['created_at'] = $item->order ? $item->order->created_at : $item->reOrder->created_at;
            $newItem['order_no'] = $item->order ? $item->order->order_no : $item->reOrder->order_no;
            $newItem['order_type'] = TellerOrders::ORDER_TYPE_LIST[$item->order_type];
            $newItem['pay_type'] = $item->getPayWay($item->pay_type); 
            
            $totalPayPrice = $item->order ? $item->order->total_pay_price : $item->reOrder->pay_price;
            $newItem['total_pay_price'] = floatval($totalPayPrice);
            $newList[] = $newItem;
        }
        
        $this->dataList = $newList;
    }
}
