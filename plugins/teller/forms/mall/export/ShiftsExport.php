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
use app\plugins\teller\models\TellerPushOrder;

class ShiftsExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'cashier_number',
                'value' => '收银员编号',
            ],
            [
                'key' => 'name',
                'value' => '姓名',
            ],
            [
                'key' => 'start_time',
                'value' => '上班时间',
            ],
            [
                'key' => 'end_time',
                'value' => '交班时间',
            ],
            [
                'key' => 'total_pay_money',
                'value' => '订单收款总额',
            ],
            [
                'key' => 'total_recharge_money',
                'value' => '会员充值总额',
            ],
            [
                'key' => 'total_refund_money',
                'value' => '退款总额',
            ],
            [
                'key' => 'store_name',
                'value' => '门店名称',
            ],
        ];

        return $fieldsList;
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query->with('cashier', 'store')->orderBy(['id' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '交班记录';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 0;
        foreach ($list as $key => $item) {
            $number++;
            $newItem = [];
            $newItem['number'] = $number;
            $newItem['cashier_number'] = $item->cashier->number;
            $newItem['name'] = $item->cashier->user->nickname;
            $newItem['start_time'] = $item->start_time;
            $newItem['end_time'] = $item->end_time;

            $extra = json_decode($item->extra_attributes, true);
            $totalRefund = isset($extra['refund']['total_refund']) ? $extra['refund']['total_refund'] : 0;
            $totalPayMoney = isset($extra['proceeds']['total_proceeds']) ? $extra['proceeds']['total_proceeds'] : 0;
            $totalRechargeMoney = isset($extra['recharge']['total_recharge']) ? $extra['recharge']['total_recharge'] : 0;
            $newItem['total_pay_money'] = (float)$totalPayMoney;
            $newItem['total_recharge_money'] = (float)$totalRechargeMoney;
            $newItem['total_refund_money'] = (float)$totalRefund;

            $newItem['store_name'] = $item->store->name;

            $newList[] = $newItem;
        }
        
        $this->dataList = $newList;
    }
}
