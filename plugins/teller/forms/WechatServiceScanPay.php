<?php

namespace app\plugins\teller\forms;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\PaymentOrderUnion;
use luweiss\Wechat\WechatException;
use luweiss\Wechat\WechatHelper;
use luweiss\Wechat\WechatHttpClient;
use luweiss\Wechat\WechatPay;

class WechatServiceScanPay extends WechatPay
{
    /**
     *
     * 付款码支付, <a href="https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1"</a>
     *
     * @return array
     * @throws WechatException
     */
    public function micropayOrder($args)
    {
        $args['spbill_create_ip'] = !empty($args['spbill_create_ip']) ? $args['spbill_create_ip'] : '127.0.0.1';

        $api = 'https://api.mch.weixin.qq.com/pay/micropay';
        return $this->send($api, $args);
    }

    /**
     *
     * 撤销订单, <a href="https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_11"</a>
     *
     * @return array
     * @throws WechatException
     */
    public function payReverse($args)
    {
        $api = 'https://api.mch.weixin.qq.com/secapi/pay/reverse';
        return $this->sendWithPem($api, $args);
    }
}
