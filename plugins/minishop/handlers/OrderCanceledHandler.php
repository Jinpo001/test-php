<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/19
 * Time: 5:16 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\plugins\minishop\forms\OrderForm;

class OrderCanceledHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CANCELED, function ($event) {
            \Yii::warning('---自定义版交易组件 订单取消事件---');
            try {
                /* @var OrderEvent $event */
                $form = new OrderForm();
                $form->order = $event->order;
                $form->type = 'cancel';
                $form->action_type = $event->action_type;
                $form->execute();
            } catch (\Exception $exception) {
                \Yii::warning($exception);
            }
            return true;
        });
    }
}
