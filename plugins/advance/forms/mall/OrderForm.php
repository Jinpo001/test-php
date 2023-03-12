<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/29
 * Time: 11:50
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\order\BaseOrderForm;
use app\plugins\advance\forms\mall\OrderExport;
use app\plugins\advance\models\AdvanceOrder;

class OrderForm extends BaseOrderForm
{
    protected function getExtra($order)
    {

    }

    protected function getFieldsList()
    {
        return (new OrderExport())->fieldsList();
    }

    public function getModelClass()
    {
        return 'app\\plugins\\advance\\forms\\mall\\OrderForm';
    }

    protected function export($query)
    {
        $queueId = CommonExport::handle([
            'export_class' => 'app\\plugins\\advance\\forms\\mall\\OrderExport',
            'params' => [
                'fieldsKeyList' => $this->fields,
                'send_type' => $this->send_type,
            ],
            'model_class' => $this->getModelClass(),
            'model_params' => ['sign' => 'advance'],
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
