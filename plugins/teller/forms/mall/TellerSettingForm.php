<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;


use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\teller\forms\common\CommonTellerSetting;

class TellerSettingForm extends Model
{
    public function getSetting()
    {
        $setting = (new CommonTellerSetting())->search();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}
