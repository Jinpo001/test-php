<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\order;

use app\core\response\ApiCode;
use app\forms\admin\PaySettingForm;
use app\forms\admin\order\BasePayment;
use app\forms\admin\order\PaymentInterface;
use luweiss\Wechat\WechatPay;

class AppWechatPay implements PaymentInterface
{
	public function getService()
	{
		$setting = (new PaySettingForm())->getOption();

        $config = [];
        if ($setting['wechat_cert_pem'] && $setting['wechat_key_pem']) {
            $this->generatePem($config, $setting['wechat_cert_pem'], $setting['wechat_key_pem']);
        }

        return new WechatPay(array_merge([
            'appId' => $setting['wechat_appid'],
            'mchId' => $setting['wechat_mchid'],
            'key' => $setting['wechat_key'],
        ], $config));
	}
    

    /**
     * @param $config
     * @param $cert_pem
     * @param $key_pem
     */
    private function generatePem(&$config, $cert_pem, $key_pem)
    {
        $pemDir = \Yii::$app->runtimePath . '/pem';
        make_dir($pemDir);
        $certPemFile = $pemDir . '/' . md5($cert_pem);
        if (!file_exists($certPemFile)) {
            file_put_contents($certPemFile, $cert_pem);
        }
        $keyPemFile = $pemDir . '/' . md5($key_pem);
        if (!file_exists($keyPemFile)) {
            file_put_contents($keyPemFile, $key_pem);
        }
        $config['certPemFile'] = $certPemFile;
        $config['keyPemFile'] = $keyPemFile;
    }

    public function getNotifyUrl()
    {
        $protocol = env('PAY_NOTIFY_PROTOCOL');
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/pay-notify/wechat-native.php';
        if ($protocol) {
            $url = str_replace('http://', ($protocol . '://'), $url);
            $url = str_replace('https://', ($protocol . '://'), $url);
        }
        return $url;
    }
}
