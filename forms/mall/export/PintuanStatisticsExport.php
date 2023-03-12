<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class PintuanStatisticsExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'name',
                'value' => '商品名称',
            ],
            [
                'key' => 'all_user',
                'value' => '拼团人数',
            ],
            [
                'key' => 'success_user',
                'value' => '成团人数',
            ],
            [
                'key' => 'success_100',
                'value' => '成团率',
            ],
            [
                'key' => 'pay_user',
                'value' => '支付人数',
            ],
            [
                'key' => 'goods_num',
                'value' => '支付件数',
            ],
            [
                'key' => 'total_pay_price',
                'value' => '支付金额',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        
        $fieldsKeyList = [];
        foreach ($this->fieldsList() as $item) {
            $fieldsKeyList[] = $item['key'];
        }
        $this->fieldsKeyList = $fieldsKeyList;

        $this->exportAction($query, ['is_array' => true]);

        return true;
    }

    public function getFileName()
    {
        return '拼团统计';
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            $arr['success_100']=(($item['all_user'] == 0) ? 0 : (bcdiv($item['success_user'], $item['all_user'], 4) * 100)) . '%';
            $item['all_user'] = intval($item['all_user']);
            $item['success_user'] = intval($item['success_user']);
            $item['pay_user'] = intval($item['pay_user']);
            $item['goods_num'] = intval($item['goods_num']);
            $item['total_pay_price'] = floatval($item['total_pay_price']);

            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
