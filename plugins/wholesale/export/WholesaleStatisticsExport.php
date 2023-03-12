<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\wholesale\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;

class WholesaleStatisticsExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'name',
                'value' => '商品名称',
            ],
            [
                'key' => 'goods_num',
                'value' => '支付件数',
            ],
            [
                'key' => 'total_price',
                'value' => '支付金额',
            ],
            [
                'key' => 'user_num',
                'value' => '支付人数',
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
        return '批发统计';
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            $arr['name'] = $item['goods']['goodsWarehouse']['name'];
            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
