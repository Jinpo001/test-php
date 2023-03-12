<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/29
 * Time: 16:42
 */

namespace app\plugins\advance\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\jobs\ExportJob;
use app\forms\mall\order\OrderForm;

class AdvanceOrderForm extends OrderForm
{
    public $flag;

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $query = $this->where();

        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $queueId = CommonExport::handle([
                'export_class' => 'app\\plugins\\advance\\forms\\mall\\OrderExport',
                'params' => [
                    'query' => $new_query,
                    'fieldsKeyList' => $this->fields,
                ]
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
}
