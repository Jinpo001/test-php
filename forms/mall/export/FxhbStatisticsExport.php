<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;

class FxhbStatisticsExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'start_time',
                'value' => '开始时间',
            ],
            [
                'key' => 'end_time',
                'value' => '结束时间',
            ],
            [
                'key' => 'name',
                'value' => '活动名称',
            ],
            [
                'key' => 'num',
                'value' => '所需人数',
            ],
            [
                'key' => 'launch_user_num',
                'value' => '发起人数',
            ],
            [
                'key' => 'participate_num',
                'value' => '参与人数',
            ],
            [
                'key' => 'launch_num',
                'value' => '发起数量',
            ],
            [
                'key' => 'success_num',
                'value' => '成功数量',
            ],
            [
                'key' => 'coupon_num',
                'value' => '发放优惠券数量',
            ],
            [
                'key' => 'coupon_price',
                'value' => '发放优惠券总金额',
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
        return '拆红包统计';
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            $item['num'] = intval($item['num']);
            $item['launch_user_num'] = intval($item['launch_user_num']);
            $item['participate_num'] = intval($item['participate_num']);
            $item['launch_num'] = intval($item['launch_num']);
            $item['success_num'] = intval($item['success_num']);
            $item['coupon_num'] = intval($item['coupon_num']);
            $item['coupon_price'] = floatval($item['coupon_price']);

            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
