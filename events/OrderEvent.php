<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/23 17:01
 */


namespace app\events;


use app\models\Order;
use yii\base\Event;

class OrderEvent extends Event
{
    /** @var Order */
    public $order;

    public $cartIds = [];

    public $pluginData;

    /**
     * @var integer $action_type
     * 订单取消状态 3--用户取消 4--超时未支付 5--商家取消
     */
    public $action_type = 5;
}
