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

class ExchangeExport extends BaseExport
{
    public $library_id;

    public function fieldsList()
    {
        return [
            [
                'key' => 'code',
                'value' => '兑换码',
            ],
            [
                'key' => 'type',
                'value' => '生成方式',
            ],
            [
                'key' => 'created_at',
                'value' => '生成时间',
            ],
            [
                'key' => 'valid_time',
                'value' => '有效期',
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
        $data = (new CommonModel())->getLibrary($this->library_id);
        return sprintf('%s-兑换码列表导出', $data->name);
    }

    protected function transform($list)
    {
        $newList = [];
        current($list) === false || $library = CommonModel::getLibrary(current($list)['library_id']);
        foreach ($list as $item) {
            $arr = [];
            $arr['code'] = $item->code;
            $arr['type'] = $item['type'] != 1 ? $item['type'] == 0 ? '手动' : '未知' : '礼品卡';
            $arr['valid_time'] = $library['expire_type'] === 'all' ? '永久' : $item['valid_start_time'] . ',' . $item['valid_end_time'];
            (new CommonModel())->getStatus($library, $item, $msg);
            $arr['status'] = $msg;
            $arr['created_at'] = $item['created_at'];
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
