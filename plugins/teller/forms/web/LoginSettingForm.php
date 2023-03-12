<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use app\models\User;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\models\TellerCashier;

class LoginSettingForm extends Model
{
    public $mall_id;

    public function rules()
    {
        return [
            [['mall_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mall_id' => '商城ID'
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->mall_id = base64_decode($this->mall_id);
            $mall = Mall::find()->andWhere(['id' => $this->mall_id, 'is_delete' => 0])->one();

            if (!$mall) {
                throw new \Exception('商城不存在');
            }

            if ($mall->is_disable) {
                throw new \Exception('商城已被禁用');
            }

            $common = new CommonTellerSetting();
            $common->mall_id = $mall->id;
            $setting = $common->search();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'name' => $mall->name,
                    'logo_url' => $setting['logo_url'],
                    'background_image_url' => $setting['background_image_url'],
                    'copyright' => $setting['copyright'],
                    'copyright_url' => $setting['copyright_url'],
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }
}
