<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\order;

use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\core\response\ApiCode;
use app\forms\admin\PaySettingForm;
use app\forms\admin\order\BasePayment;
use app\forms\admin\order\PaymentInterface;

class AppAlipayPay implements PaymentInterface
{
	public function getService()
	{
		$setting = (new PaySettingForm())->getOption();

        $aop = new AopClient(
            $setting['alipay_app_id'],
            AlipayKeyPair::create($setting['alipay_private_key'], $setting['alipay_public_key'])
        );
        return $aop;
	}

    public function getNotifyUrl()
    {
        $protocol = env('PAY_NOTIFY_PROTOCOL');
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/pay-notify/alipay-native.php';
        if ($protocol) {
            $url = str_replace('http://', ($protocol . '://'), $url);
            $url = str_replace('https://', ($protocol . '://'), $url);
        }
        return $url;
    }
}
