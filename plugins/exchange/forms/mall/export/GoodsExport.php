<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\exchange\forms\mall\export;

use app\core\CsvExport;
use app\core\response\ApiCode;
use app\forms\mall\export\BaseExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\exchange\forms\common\CommonModel;

class GoodsExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'id',
                'value' => 'id',
            ],
            [
                'key' => 'name',
                'value' => '礼品卡名称',
            ],
            [
                'key' => 'price',
                'value' => '价格',
            ],
            [
                'key' => 'library_name',
                'value' => '兑换码库',
            ],
            [
                'key' => 'goods_stock',
                'value' => '库存',
            ],
            [
                'key' => 'sales',
                'value' => '已出售量',
            ],
            [
                'key' => 'created_at',
                'value' => '添加时间',
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
        $this->fieldsKeyList = array_column($this->fieldsList(), 'key');
        
        $this->exportAction($query);

        return true;
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        return '礼品卡-导出';
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['name'] = $item->name;
            $arr['price'] = $item->price;
            $arr['library_name'] = $item->library->name;
            $arr['goods_stock'] = $item->goods_stock;
            $arr['sales'] = intval($item->sales) + intval($item->virtual_sales);
            $arr['created_at'] = $item->created_at;
            $arr['status'] = $item->status == 1 ? '销售中' : '下架';
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
