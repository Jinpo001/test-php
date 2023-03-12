<?php

namespace app\forms\admin\order;

use app\forms\admin\order\AppAlipayPay;
use app\forms\admin\order\AppWechatPay;

class AppPayment 
{
    private $instance;

    public static function getInstance(string $drives)
    {
        switch ($drives) {
            case 'wechat':
                    return new AppWechatPay();
                break;
            case 'alipay':
                    return new AppAlipayPay();
                break;
            
            default:
                throw new \Exception('支付方式不存在');
                break;
        }
    }
}