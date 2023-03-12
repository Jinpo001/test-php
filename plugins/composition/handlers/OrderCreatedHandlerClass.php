<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/23
 * Time: 5:45 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\composition\handlers;

use app\plugins\composition\Plugin;

class OrderCreatedHandlerClass extends \app\handlers\orderHandler\OrderCreatedHandlerClass
{
    public function setShareMoney()
    {
        \Yii::warning('--创建订单  套餐组合--');
        $plugin = new Plugin();
        $orderConfig = $plugin->getOrderConfig();
        if ($orderConfig['is_share'] == 1) {
            return parent::setShareMoney();
        } else {
            return $this;
        }
    }
}
