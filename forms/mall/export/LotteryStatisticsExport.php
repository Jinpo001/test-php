<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class LotteryStatisticsExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'name',
                'value' => '商品名称',
            ],
            [
                'key' => 'attr_groups',
                'value' => '规格',
            ],
            [
                'key' => 'start_at',
                'value' => '开始时间',
            ],
            [
                'key' => 'end_at',
                'value' => '结束时间',
            ],
            [
                'key' => 'invitee',
                'value' => '被邀请人数',
            ],
            [
                'key' => 'participant',
                'value' => '参与人数',
            ],
            [
                'key' => 'code_num',
                'value' => '抽奖劵码数量',
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
        return '幸运抽奖统计';
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            $item['attr_groups'] = $this->attr_groups($item['attr_groups']);
            $item['participant'] = intval($item['participant']);
            $item['invitee'] = intval($item['invitee']);
            $item['code_num'] = intval($item['code_num']);

            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }

    protected function attr_groups($value)
    {
        $value = json_decode($value, true);
        if (is_array($value)) {
            $attr = '';
            foreach ($value as $v) {
                $attr .= $v['attr_group_name'] . ':' . $v['attr_list'][0]['attr_name'] . ' ';
            }
        }
        return $attr;
    }
}
