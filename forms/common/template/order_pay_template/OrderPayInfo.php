<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 2:32 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\OrderPayTemplate;

class OrderPayInfo extends BaseInfo
{
    public const TPL_NAME = 'order_pay_tpl';
    protected $key = 'store';
    protected $chineseName = '下单成功提醒';

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '434',
                    'keyword_id_list' => [6, 5, 9, 1],
                    'title' => '下单成功提醒',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'character_string6' => '',
                        'date5' => '',
                        'amount9' => '',
                        'thing1' => '',
                    ],
                ],
                'local' => [
                    'name' => '下单成功提醒(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/order_pay_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单支付成功通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/order_pay_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0221',
                    'keyword_id_list' => [2, 9, 81, 34],
                    'title' => '下单成功通知'
                ],
                'local' => [
                    'name' => '下单成功通知(模板编号: BD0221 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/order_pay_tpl.png'
                ]
            ],
            'ttapp' => [
                'local' => [
                    'name' => '新订单通知',
                    'img_url' => $iconUrlPrefix . 'ttapp/order_pay_tpl.png',
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM416685404',
                    'keyword_id_list' => 'OPENTM416685404',
                    'title' => '下单成功通知'
                ],
                'local' => [
                    'name' => '下单成功通知',
                    'img_url' => $iconUrlPrefix . 'wechat/order_pay_tpl.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '下单成功通知',
                    'content' => '例如：亲爱的会员，您在商城的订单提交成功。我们会尽快发货，记得关注我们的商城喔～感谢您的支持！',
                    'support_mch' => true,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：亲爱的会员，您在${name}的订单提交成功。我们会尽快发货，记得关注我们的商城喔～感谢您的支持！则填写name'
                        ]
                    ],
                    'key' => 'user'
                ]
            ]
        ];
    }

    public function getSendClass()
    {
        return new OrderPayTemplate();
    }
}
