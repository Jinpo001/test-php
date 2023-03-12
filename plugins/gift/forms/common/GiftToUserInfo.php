<?php

namespace app\plugins\gift\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;
use app\plugins\gift\forms\common\GiftToUserTemplate;


class GiftToUserInfo extends BaseInfo
{
    public const TPL_NAME = 'gift_to_user';
    protected $key = 'gift';
    protected $chineseName = '礼物即将超时通知';

    public function getSendClass()
    {
        return new GiftToUserTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'aliapp' => [
                'local' => [
                    'name' => '订单状态通知（模板编号：AT0056）',
                    'img_url' => $iconUrlPrefix . 'aliapp/gift_fail_tpl.png',
                ],
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0545',
                    'keyword_id_list' => [
                        7,
                        9,
                        4,
                        15,
                    ],
                    'title' => '订单超时提醒',
                ],
                'local' => [
                    'name' => '订单超时提醒（模板编号：BD0545）',
                    'img_url' => $iconUrlPrefix . 'bdapp/gift_timeout_tpl.png'
                ]
            ]

        ];
    }
}
