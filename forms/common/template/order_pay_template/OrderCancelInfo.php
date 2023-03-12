<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:11 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\OrderCancelTemplate;

class OrderCancelInfo extends BaseInfo
{
    public const TPL_NAME = 'order_cancel_tpl';
    protected $key = 'store';
    protected $chineseName = '订单取消通知';

    public function getSendClass()
    {
        return new OrderCancelTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '994',
                    'keyword_id_list' => [12, 1, 4, 7],
                    'title' => '订单取消通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing12' => '',
                        'character_string1' => '',
                        'amount4' => '',
                        'thing7' => '',
                    ],
                ],
                'local' => [
                    'name' => '订单取消通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/order_cancel_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单取消通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/order_cancel_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0021',
                    'keyword_id_list' => [24, 5, 4, 17],
                    'title' => '订单取消通知'
                ],
                'local' => [
                    'name' => '订单取消(模板编号: BD0021 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/order_cancel_tpl.png'
                ]
            ],
            'ttapp' => [
                'local' => [
                    'name' => '订单取消通知',
                    'img_url' => $iconUrlPrefix . 'ttapp/order_cancel_tpl.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM406411654',
                    'keyword_id_list' => 'OPENTM406411654',
                    'title' => '订单取消通知'
                ],
                'local' => [
                    'name' => '订单取消通知',
                    'img_url' => $iconUrlPrefix . 'wechat/order_cancel_tpl.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '下单取消通知',
                    'content' => '例如：亲爱的会员，您的尾号为123456的订单已被取消。',
                    'support_mch' => true,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：亲爱的会员，您的尾号为${name}的订单已被取消。则填写name'
                        ]
                    ],
                    'key' => 'user'
                ]
            ]
        ];
    }
}
