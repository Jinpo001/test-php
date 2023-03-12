<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall\export;

use app\core\response\ApiCode;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\exchange\forms\common\CommonModel;

class CodeExport
{
    public $page = 1;
    public const line = 500;

    public function export(BaseActiveQuery $query)
    {
        $list = $query->page($pagination, self::line, $this->page)->all();
        $list = $this->transform($list);

        if ($this->page >= $pagination->page_count) {
            header('D-Status: close');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'pagination' => $pagination,
                    'list' => $list,
                ]
            ];
        } else {
            header('D-Status: connection');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '获取成功',
                'data' => [
                    'pagination' => $pagination,
                    'list' => $list,
                ]
            ];
        }
    }

    protected function transform($list)
    {
        current($list) === false || $library = CommonModel::getLibrary(current($list)['library_id']);
        foreach ($list as $item) {
            (new CommonModel())->getStatus($library, $item, $msg);
            $arr = [];
            $arr['兑换码'] = $item->code . "\t";
            $arr['生成方式'] = $item['type'] != 1 ? $item['type'] == 0 ? '手动' : '未知' : '礼品卡';
            $arr['有效期'] = $library['expire_type'] === 'all' ? '永久' : $item['valid_start_time'] . ',' . $item['valid_end_time'];
            $arr['状态'] = $msg;
            $arr['生成时间'] = $item['created_at'] . "\t";
            yield $arr;
        }
    }
}
