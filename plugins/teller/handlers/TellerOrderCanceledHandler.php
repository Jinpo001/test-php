<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/4/12
 * Time: 10:58
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\teller\handlers;

use app\handlers\orderHandler\OrderCanceledHandlerClass;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\plugins\teller\forms\OrderQueryForm;

class TellerOrderCanceledHandler extends OrderCanceledHandlerClass
{
    public function handle()
    {
        $this->user = $this->event->order->user;
        $this->cancel();
    }
}
