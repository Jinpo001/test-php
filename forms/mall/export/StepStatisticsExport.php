<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class StepStatisticsExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'begin_at',
                'value' => '活动时间',
            ],
            [
                'key' => 'title',
                'value' => '活动名称',
            ],
            [
                'key' => 'step_num',
                'value' => '挑战步数',
            ],
            [
                'key' => 'participate_num',
                'value' => '报名人数',
            ],
            [
                'key' => 'success_num',
                'value' => '挑战成功人数',
            ],
            [
                'key' => 'currency',
                'value' => '奖金池总额',
            ],
            [
                'key' => 'put_currency',
                'value' => '报名活力币消耗',
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
        return '步数挑战统计';
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            // $arr['success_100'] = (($item['all_user'] == 0) ? 0 : (bcdiv($item['success_user'], $item['all_user'], 4) * 100)) . '%';
            $item['step_num'] = intval($item['step_num']);
            $item['participate_num'] = intval($item['participate_num']);
            $item['success_num'] = intval($item['success_num']);
            $item['currency'] = floatval($item['currency']);
            $item['put_currency'] = intval($item['put_currency']);

            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
