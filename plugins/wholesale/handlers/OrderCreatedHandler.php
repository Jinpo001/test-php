<?php

namespace app\plugins\wholesale\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Model;
use app\models\Order;
use app\plugins\flash_sale\models\FlashSaleOrderDiscount;
use app\plugins\wholesale\models\WholesaleOrder;
use Exception;
use Yii;

class OrderCreatedHandler extends HandlerBase
{
    public function register()
    {
        Yii::$app->on(
            Order::EVENT_CREATED,
            function ($event) {
                /**@var OrderEvent $event * */
                if (isset($event->pluginData['wholesale_discount']) && !empty($event->pluginData['wholesale_discount'])) {
                    $model = new WholesaleOrder();
                    $model->mall_id = $event->order->mall_id;
                    $model->order_id = $event->order->id;
                    $model->discount = $event->pluginData['wholesale_discount'];
                    if (!$model->save()) {
                        throw new Exception((new Model())->getErrorMsg($model));
                    }
                }
            }
        );
    }
}
