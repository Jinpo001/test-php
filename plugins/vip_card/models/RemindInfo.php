<?php

namespace app\plugins\vip_card\models;

use app\forms\common\template\order_pay_template\BaseInfo;

class RemindInfo extends BaseInfo
{
    public const TPL_NAME = 'vip_card_remind';
    protected $key = 'vip_card';
    protected $chineseName = '会员卡到期提醒';

    public function getSendClass()
    {
        return new RemindTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '3572',
                    'keyword_id_list' => [
                        2,
                        3,
                    ],
                    'title' => '会员卡到期提醒',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'phrase2' => '',
                        'date3' => '',
                    ],
                ],
                'local' => [
                    'name' => '会员卡到期提醒（类目: 服装/鞋/箱包 ）',
                    'img_url' => $iconUrlPrefix . 'wxapp/svip_expire_tpl.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '服务进度通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/svip_expire_tpl.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD1382',
                    'keyword_id_list' => [
                        2,
                        4,
                    ],
                    'title' => '产品到期通知',
                ],
                'local' => [
                    'name' => '产品到期通知（模板编号：BD1382）',
                    'img_url' => $iconUrlPrefix . 'bdapp/svip_expire_tpl.png',
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM401132955',
                    'keyword_id_list' => 'OPENTM401132955',
                    'title' => '会员资格失效通知',
                ],
                'local' => [
                    'name' => '会员资格失效通知',
                    'img_url' => $iconUrlPrefix . 'wechat/svip_expire_tpl.png',
                ]
            ],
        ];
    }
}
