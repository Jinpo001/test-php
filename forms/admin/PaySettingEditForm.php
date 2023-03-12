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

class PaySettingEditForm extends Model
{
    public $pay_list;
    public $wechat_appid;
    public $wechat_mchid;
    public $wechat_key;
    public $wechat_cert_pem;
    public $wechat_key_pem;
    public $alipay_app_id;
    public $alipay_public_key;
    public $alipay_private_key;
    public $alipay_appcert;
    public $alipay_rootcert;
    public $customer_service_list;

    public function rules()
    {
        return [
            [['wechat_appid', 'wechat_mchid', 'wechat_key', 'wechat_cert_pem', 'wechat_key_pem', 'alipay_app_id', 'alipay_public_key', 'alipay_private_key', 'alipay_appcert', 'alipay_rootcert'], 'string'],
            [['pay_list', 'customer_service_list'], 'safe'],
            ['alipay_private_key', function () {
                $this->alipay_private_key = $this->addBeginAndEnd(
                    '-----BEGIN RSA PRIVATE KEY-----',
                    '-----END RSA PRIVATE KEY-----',
                    $this->alipay_private_key
                );
            }]
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $data = $this->attributes;
            $setting = CommonOption::set('admin_pay_setting', $data, 0, Option::GROUP_ADMIN);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    private function addBeginAndEnd($beginStr, $endStr, $data)
    {
        $data = $this->pregReplaceAll('/---.*---/', '', $data);
        $data = trim($data);
        $data = str_replace("\n", '', $data);
        $data = str_replace("\r\n", '', $data);
        $data = str_replace("\r", '', $data);
        $data = wordwrap($data, 64, "\r\n", true);

        if (mb_stripos($data, $beginStr) === false) {
            $data = $beginStr . "\r\n" . $data;
        }
        if (mb_stripos($data, $endStr) === false) {
            $data = $data . "\r\n" . $endStr;
        }
        return $data;
    }

    private function pregReplaceAll($find, $replacement, $s)
    {
        while (preg_match($find, $s)) {
            $s = preg_replace($find, $replacement, $s);
        }
        return $s;
    }
}
