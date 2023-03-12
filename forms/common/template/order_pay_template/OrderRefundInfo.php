<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:26 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\OrderRefund;

class OrderRefundInfo extends BaseInfo
{
    public const TPL_NAME = 'order_refund_tpl';
    protected $key = 'store';
    protected $chineseName = '订单退款通知';

    public function getSendClass()
    {
        return new OrderRefund();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '1435',
                    'keyword_id_list' => [4, 5, 2, 1],
                    'title' => '退款通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'character_string4' => '',
                        'thing5' => '',
                        'amount2' => '',
                        'thing1' => '',
                    ],
                ],
                'local' => [
                    'name' => '退款通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/order_refund_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '退款通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/order_refund_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0022',
                    'keyword_id_list' => [33, 13, 3, 4],
                    'title' => '退款通知'
                ],
                'local' => [
                    'name' => '订单退款(模板编号: BD0022 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/order_refund_tpl.png'
                ]
            ],
            'ttapp' => [
                'name' => '退款通知',
                'img_url' => $iconUrlPrefix . 'ttapp/order_refund_tpl.png'
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM413279802',
                    'keyword_id_list' => 'OPENTM413279802',
                    'title' => '退款完成通知'
                ],
                'local' => [
                    'name' => '退款完成通知',
                    'img_url' => $iconUrlPrefix . 'wechat/order_refund_tpl.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '退款进度通知',
                    'content' => '例如：您申请的售后请求已被通过，请进商城查看。',
                    'support_mch' => true,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：您申请的售后请求已被${name}，请进商城查看。则填写name'
                        ]
                    ],
                    'key' => 'user'
                ],
            ]
        ];
    }
}
