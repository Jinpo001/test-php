<?php

namespace app\plugins\dianqilai\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;


class CommonInfo extends BaseInfo
{
    public const TPL_NAME = 'contact_tpl';
    protected $key = 'dianqilai';
    protected $chineseName = '留言回复通知';

    public function getSendClass()
    {
        return new CommonTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '6773',
                    'keyword_id_list' => [
                        1,
                        2,
                        3,
                    ],
                    'title' => '留言回复通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'name1' => '',
                        'date2' => '',
                        'thing3' => '',
                    ],
                ],
                'local' => [
                    'name' => '留言回复通知(类目: 服装/鞋/箱包)',
                    'img_url' => $iconUrlPrefix . 'wxapp/contact_tpl.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '信息处理提醒',
                    'img_url' => $iconUrlPrefix . 'aliapp/account_change_tpl.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD1941',
                    'keyword_id_list' => [
                        1,
                        4,
                        5,
                    ],
                    'title' => '咨询回复通知',
                ],
                'local' => [
                    'name' => '咨询回复通知(模板编号：BD1941)',
                    'img_url' => $iconUrlPrefix . 'bdapp/contact_tpl.png',
                ],
            ]
        ];
    }
}
