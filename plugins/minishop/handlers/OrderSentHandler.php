<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/20
 * Time: 2:39 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\handlers;

use app\events\OrderEvent;
use app\handlers\HandlerBase;
use app\models\Order;
use app\plugins\minishop\forms\OrderForm;

class OrderSentHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_SENT, function ($event) {
            try {

                \Yii::warning('---自定义版交易组件 订单发货事件---');
                /* @var OrderEvent $event */
                $form = new OrderForm();
                $form->order = $event->order;
                $form->type = 'send';
                $form->execute();
            } catch (\Exception $exception) {
                \Yii::warning($exception);
            }
            return true;
        });
    }
}
