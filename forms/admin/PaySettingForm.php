<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class PaySettingForm extends Model
{
    public function rules()
    {
        return [];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function getSetting()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $setting = $this->getOption();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'setting' => $setting
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

    public function getOption()
    {
        $setting = CommonOption::get('admin_pay_setting', 0, Option::GROUP_ADMIN, $this->getDefault());

        if ($setting['customer_service_list']) {
            foreach ($setting['customer_service_list'] as &$item) {
                $item['is_all_day'] = $item['is_all_day'] == 'true' ? true : false;
            }
            unset($item);
        }

        $setting['pay_list'] = $setting['pay_list'] ?: [];
        $setting['customer_service_list'] = $setting['customer_service_list'] ?: [];

        return $setting;
    }

    public function getDefault()
    {
        return [
            'pay_list' =>  [],
            'wechat_appid' =>  '',
            'wechat_mchid' =>  '',
            'wechat_key' =>  '',
            'wechat_cert_pem' => '',
            'wechat_key_pem' => '',
            'alipay_app_id' => '',
            'alipay_public_key' => '',
            'alipay_private_key' => '',
            'alipay_appcert' => '',
            'alipay_rootcert' => '',
            'customer_service_list' => []
        ];
    }
}
