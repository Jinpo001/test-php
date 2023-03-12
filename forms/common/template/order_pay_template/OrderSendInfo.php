<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:20 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\OrderSendTemplate;

class OrderSendInfo extends BaseInfo
{
    public const TPL_NAME = 'order_send_tpl';
    protected $key = 'store';
    protected $chineseName = '订单发货通知';

    public function getSendClass()
    {
        return new OrderSendTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '855',
                    'keyword_id_list' => [2, 7, 4, 8],
                    'title' => '订单发货通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing2' => '',
                        'thing7' => '',
                        'character_string4' => '',
                        'thing8' => '',
                    ],
                ],
                'local' => [
                    'name' => '订单发货通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/order_send_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单发货提醒',
                    'img_url' => $iconUrlPrefix . 'aliapp/order_send_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0003',
                    'keyword_id_list' => [5, 2, 23, 55],
                    'title' => '订单发货提醒'
                ],
                'local' => [
                    'name' => '订单发货(模板编号: BD0003 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/order_send_tpl.png'
                ]
            ],
            'ttapp' => [
                'local' => [
                    'name' => '订单发货',
                    'img_url' => $iconUrlPrefix . 'ttapp/order_send_tpl.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM414274800',
                    'keyword_id_list' => 'OPENTM414274800',
                    'title' => '发货提醒'
                ],
                'local' => [
                    'name' => '发货提醒',
                    'img_url' => $iconUrlPrefix . 'wechat/order_send_tpl.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '发货通知',
                    'content' => '例如：亲爱的用户，您的尾号为123456的订单已经发出，请注意查收。',
                    'support_mch' => true,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：亲爱的用户，您的尾号为${name}的订单已经发出，请注意查收。则填写name'
                        ]
                    ],
                    'key' => 'user'
                ],
            ]
        ];
    }
}
