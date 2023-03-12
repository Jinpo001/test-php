<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class SendStatisticsExport extends BaseExport
{

    public $type;

    public function fieldsList()
    {
        return [
            [
                'key' => 'date',
                'value' => '日期',
            ],
            [
                'key' => 'name',
                'value' => '名称',
            ],
            [
                'key' => 'all_num',
                'value' => '发放总数',
            ],
            [
                'key' => 'use_num',
                'value' => '已使用数量',
            ],
            [
                'key' => 'unuse_num',
                'value' => '未使用数量',
            ],
            [
                'key' => 'end_num',
                'value' => '已失效数量',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query->groupBy('`date`,name');
            
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
        if ($this->type == 'card') {
            $name = '卡券-';
        } else {
            $name = '优惠券-';
        }

        $fileName = $name . '发放统计';

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            $item['all_num'] = intval($item['all_num']);
            $item['use_num'] = intval($item['use_num']);
            $item['unuse_num'] = intval($item['unuse_num']);
            $item['end_num'] = intval($item['end_num']);

            $arr = array_merge($arr, $item);


            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
