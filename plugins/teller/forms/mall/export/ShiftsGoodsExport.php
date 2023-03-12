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

class ShiftsGoodsExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'goods_id',
                'value' => '商品ID',
            ],
            [
                'key' => 'goods_name',
                'value' => '商品名称',
            ],
            [
                'key' => 'goods_price',
                'value' => '售价',
            ],
            [
                'key' => 'num',
                'value' => '数量',
            ],
            [
                'key' => 'total_price',
                'value' => '商品小计',
            ],
            
        ];

        return $fieldsList;
    }

    public function export($query = null)
    {
        $query = $this->query;

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '交班商品列表';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 0;
        foreach ($list as $key => $item) {
            $number++;
            $newItem = [];
            $newItem['number'] = $number;

            $goodsInfo = json_decode($item->goods_info, true);
            $attr = '';
            foreach ($goodsInfo['attr_list'] as $attrKey => $attrValue) {
                $attr = $attr ? sprintf('%s,%s:%s', $attr, $attrValue['attr_group_name'], $attrValue['attr_name']) : sprintf('%s:%s', $attrValue['attr_group_name'], $attrValue['attr_name']);
            }
            $newItem['goods_id'] = $item->goods_id;
            $newItem['goods_name'] = $goodsInfo['goods_attr']['name'];
            $newItem['goods_price'] = (float)$goodsInfo['goods_attr']['price'];
            $newItem['num'] = $item->num;
            $newItem['total_price'] = (float)$item->total_price;
            $newList[] = $newItem;
        }
        
        $this->dataList = $newList;
    }
}
