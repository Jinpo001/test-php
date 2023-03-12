<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\forms\mall\order\BaseOrderForm;

class OrderForm extends BaseOrderForm
{
    protected function getFieldsList()
    {
        return (new OrderExport())->fieldsList();
    }

    public function getModelClass()
    {
        return 'app\\plugins\\mch\\forms\\mall\\OrderForm';
    }

    protected function export($query)
    {
        $queueId = CommonExport::handle([
            'export_class' => 'app\\plugins\\mch\\forms\\mall\\OrderExport',
            'params' => [
                'fieldsKeyList' => $this->fields,
                'send_type' => $this->send_type,
            ],
            'model_class' => $this->getModelClass(),
            'model_params' => ['is_mch' => true],
            'function_name' => 'getAllQuery',
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'queue_id' => $queueId
            ]
        ];
    }
    
    public function getSendTypeList()
    {
        $list = [
            ['value' => -1, 'name' => '全部订单'],
            ['value' => 0, 'name' => '快递配送'],
            ['value' => 1, 'name' => '到店核销']
        ];

        return $list;
    }
}