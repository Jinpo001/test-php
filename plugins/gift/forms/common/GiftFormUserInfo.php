<?php

namespace app\plugins\gift\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;


class GiftFormUserInfo extends BaseInfo
{
    public const TPL_NAME = 'gift_form_user';
    protected $key = 'gift';
    protected $chineseName = '礼物未成功送达通知';

    public function getSendClass()
    {
        return new GiftFromUserTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '992',
                    'keyword_id_list' => [
                        1,
                        2,
                        3,
                    ],
                    'title' => '商品送达通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'character_string1' => '',
                        'thing2' => '',
                        'thing3' => '',
                    ],
                ],
                'local' => [
                    'name' => '商品送达通知（类目: 服装/鞋/箱包 ）',
                    'img_url' => $iconUrlPrefix . 'wxapp/gift_timeout_tpl.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '礼物未成功送达通知（模板编号：AT0049）',
                    'img_url' => $iconUrlPrefix . 'aliapp/gift_timeout_tpl.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0545',
                    'keyword_id_list' => [
                        7,
                        9,
                        15,
                    ],
                    'title' => '订单超时提醒',
                ],
                'local' => [
                    'name' => '礼物未成功送达通知（模板编号：BD0545）',
                    'img_url' => $iconUrlPrefix . 'bdapp/gift_timeout_tpl.png',
                ],
            ],
            'ttapp' => [
                'local' => [
                    'name' => '礼物未成功送达通知（模板编号：A1）',
                    'img_url' => $iconUrlPrefix . 'ttapp/none.png'
                ],
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM401692865',
                    'keyword_id_list' => 'OPENTM401692865',
                    'title' => '订单送达通知',
                ],
                'local' => [
                    'name' => '订单送达通知',
                    'img_url' => $iconUrlPrefix . 'wechat/gift_timeout_tpl.png',
                ],
            ],

        ];
    }
}
