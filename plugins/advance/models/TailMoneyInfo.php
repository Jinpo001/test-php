<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 5:48 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\advance\models;


use app\forms\common\template\order_pay_template\BaseInfo;
use app\plugins\advance\models\TailMoneyTemplate;

class TailMoneyInfo extends BaseInfo
{
    public const TPL_NAME = 'pay_advance_balance';
    protected $key = 'advance';
    protected $chineseName = '尾款支付提醒';

    public function getSendClass()
    {
        return new TailMoneyTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '2956',
                    'keyword_id_list' => [6, 2, 4],
                    'title' => '商品到货通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing6' => '',
                        'amount2' => '',
                        'thing4' => '',
                    ]
                ],
                'local' => [
                    'name' => '商品到货通知（类目: 服装/鞋/箱包 ）',
                    'img_url' => $iconUrlPrefix . 'wxapp/tailmoney_pay_tpl.png'
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '尾款支付提醒',
                    'img_url' => $iconUrlPrefix . 'aliapp/tailmoney_pay_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0768',
                    'keyword_id_list' => [6, 2, 4, 5],
                    'title' => '尾款支付提醒'
                ],
                'local' => [
                    'name' => '尾款支付提醒（模板编号：BD0768）',
                    'img_url' => $iconUrlPrefix . 'bdapp/tailmoney_pay_tpl.png'
                ]
            ]
        ];
    }
}
