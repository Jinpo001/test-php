<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 10:14
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\BaseTemplate;

/**
 * Class TailMoneyTemplate
 * @package app\plugins\advance\models
 * 尾款支付通知
 */
class TailMoneyTemplate extends BaseTemplate
{
    public $price; // 尾款金额
    public $goodsName; // 商品名称
    public $desc = '错过支付尾款订单会被取消，定金不退哦'; // 商品名称
    public $time; // 结束时间
    protected $templateTpl = 'pay_advance_balance';

    public function msg()
    {
        return [
            'keyword1' => [
                'value' => $this->goodsName,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->price,
                'color' => '#333333',
            ],
            'keyword3' => [
                'value' => $this->desc,
                'color' => '#333333',
            ],
        ];
    }

    public function test()
    {
        $this->goodsName = 'xx联名款秋冬长裙';
        $this->price = '10元';
        return $this->send();
    }

    public function wechat()
    {
        return [
            'first' => [
                'value' => $this->desc,
                'color' => '#333333',
            ],
            'keyword1' => [
                'value' => $this->time,
                'color' => '#333333',
            ],
            'keyword2' => [
                'value' => $this->goodsName,
                'color' => '#333333',
            ],
            'remark' => [
                'value' => '请点击进行前往购买',
                'color' => '#333333',
            ],
        ];
    }
}
