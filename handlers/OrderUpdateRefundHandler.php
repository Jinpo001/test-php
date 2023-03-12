<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/22
 * Time: 10:02 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;

use app\events\OrderRefundEvent;
use app\models\OrderRefund;

class OrderUpdateRefundHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(OrderRefund::EVENT_UPDATE_REFUND, function ($event) {
            /** @var OrderRefundEvent $event */
            \Yii::warning('---订单更新后事件处理开始---');
        });
    }
}
