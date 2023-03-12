<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\scratch\forms\mall;


use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\forms\mall\order\OrderForm;

class ScratchOrderForm extends OrderForm
{
    protected function getFieldsList()
    {
        return (new OrderExport())->fieldsList();
    }

    public function getModelClass()
    {
        return 'app\\plugins\\scratch\\forms\\mall\\ScratchOrderForm';
    }

    protected function export($query)
    {
        $queueId = CommonExport::handle([
            'export_class' => 'app\\plugins\\scratch\\forms\\mall\\OrderExport',
            'params' => [
                'fieldsKeyList' => $this->fields,
                'send_type' => $this->send_type,
            ],
            'model_class' => $this->getModelClass(),
            'model_params' => ['sign' => 'scratch'],
            'function_name' => 'getAllQuery'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'queue_id' => $queueId
            ]
        ];
    }
}
