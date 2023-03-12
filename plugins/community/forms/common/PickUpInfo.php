<?php

namespace app\plugins\community\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;
use app\plugins\community\forms\common\PickUpTemplate;

class PickUpInfo extends BaseInfo
{
    public const TPL_NAME = 'pick_up_tpl';
    protected $key = 'community';
    protected $chineseName = '订单提货通知';

    public function getSendClass()
    {
        return new PickUpTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '2306',
                    'keyword_id_list' => [
                        1,
                        5,
                        3,
                    ],
                    'title' => '订单提货通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'character_string1' => '',
                        'thing5' => '',
                        'character_string3' => '',
                    ],
                ],
                'local' => [
                    'name' => '订单提货通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/pick_up_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单状态通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/pick_up_tpl.png',
                ],
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0782',
                    'keyword_id_list' => [
                        11,
                        10,
                        6,
                    ],
                    'title' => '提货通知',
                ],
                'local' => [
                    'name' => '提货通知(模板编号：BD0782)',
                    'img_url' => $iconUrlPrefix . 'bdapp/pick_up_tpl.png',
                ]
            ],
            'ttapp' => [
                'local' => [
                    'name' => '快递派件通知(模板编号：A1)',
                    'img_url' => $iconUrlPrefix . 'ttapp/pick_up_tpl.png',
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM204618157',
                    'keyword_id_list' => 'OPENTM204618157',
                    'title' => '提货通知',
                ],
                'local' => [
                    'name' => '提货通知',
                    'img_url' => $iconUrlPrefix . 'wechat/pick_up_tpl.png',
                ],
            ],
            'mobile' => [
                'local' => [
                    'title' => '商品提货通知',
                    'content' => '例如：您购买的商品已到自提点，请尽快来提货。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [],
                    'key' => 'user'
                ]
            ]
        ];
    }
}
