<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/5
 * Time: 10:14 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;

use app\models\Model;

class ShopFactory extends Model
{
    /**
     * @var RegisterService $register
     */
    public $register;

    /**
     * @var GoodsService $goods
     */
    public $goods;

    /**
     * @var OrderService $order
     */
    public $order;

    /**
     * @var DeliveryService $delivery
     */
    public $delivery;

    /**
     * @var SaleService $sale
     */
    public $sale;

    public function serviceList()
    {
        return [
            'register' => '\app\plugins\wxapp\models\shop\RegisterService',
            'goods' => '\app\plugins\wxapp\models\shop\GoodsService',
            'order' => '\app\plugins\wxapp\models\shop\OrderService',
            'delivery' => '\app\plugins\wxapp\models\shop\DeliveryService',
            'sale' => '\app\plugins\wxapp\models\shop\SaleService',
        ];
    }

    public static function create($config)
    {
        $instance = new self();
        foreach ($instance->serviceList() as $service => $class) {
            $instance->$service = \Yii::createObject([
                'class' => $class,
                'accessToken' => $config['access_token']
            ]);
        }
        return $instance;
    }
}
