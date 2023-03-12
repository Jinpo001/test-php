<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/7/25
 * Time: 10:53
 */

namespace app\forms\api\order\strategy;

/**
 * 实现循环满减
 * Class DiscountStrategy
 * @package app\forms\api\order\strategy
 */
class FullReduceLoopStrategy implements FullReduceStrategyAbstract
{
    /**
     * @param \app\models\FullReduceActivity $activity
     * @param $mchItem
     * @param $totalGoodsOriginalPrice
     * @param $totalGoodsPrice
     * @return mixed|void
     */
    public function discount($activity, $mchItem, $totalGoodsOriginalPrice, $totalGoodsPrice)
    {
        $rule = \Yii::$app->serializer->decode($activity->loop_discount_rule);
        if (!isset($rule['cut']) || !isset($rule['min_money'])) {
            return 0;
        }
        if ($totalGoodsPrice < $rule['min_money']) {
            return 0;
        }
        //循环满减
        return intval($totalGoodsPrice / $rule['min_money']) * $rule['cut'];
    }
    /**
     * @param \app\models\FullReduceActivity $activity
     * @param $mchItem
     * @param $totalGoodsOriginalPrice
     * @param $totalGoodsPrice
     * @return mixed|void
     */
    public function nextDiscount($activity, $mchItem, $totalGoodsOriginalPrice, $totalGoodsPrice)
    {
        $rule = \Yii::$app->serializer->decode($activity->loop_discount_rule);
        if (!isset($rule['cut']) || !isset($rule['min_money'])) {
            return 0;
        }
        $loop = intval($totalGoodsPrice / $rule['min_money']) + 1;
        $res = [
            'diff' => price_format($loop * $rule['min_money'] - $totalGoodsPrice),
            'min_money' => price_format($loop * $rule['min_money']),
            'sub' => price_format($loop * $rule['cut']),
            'discount_type' => 1
        ];
        $res['text'] = '满' . $res['min_money'] . '减' . $res['sub'] . '元';
        //循环满减
        return $res;
    }
}
