<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/5/11
 * Time: 14:52
 */

namespace app\plugins\flash_sale\forms\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;

class FlashSaleStatisticsExport extends BaseExport
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
        return '限时抢购统计';
    }

    protected function transform($list)
    {
        $newList = [];
        $arr = [];

        foreach ($list as $key => $item) {
            $item['pay_user'] = intval($item['pay_user']);
            $item['goods_num'] = intval($item['goods_num']);
            $item['total_pay_price'] = floatval($item['total_pay_price']);
            $arr = array_merge($arr, $item);

            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
