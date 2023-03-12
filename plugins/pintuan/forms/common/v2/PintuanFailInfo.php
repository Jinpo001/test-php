<?php

namespace app\plugins\pintuan\forms\common\v2;

use app\forms\common\template\order_pay_template\BaseInfo;
use app\plugins\pintuan\forms\common\v2\PintuanFailTemplate;

class PintuanFailInfo extends BaseInfo
{
    public const TPL_NAME = 'pintuan_fail_notice';
    protected $key = 'pintuan';
    protected $chineseName = '拼团失败通知';

    public function getSendClass()
    {
        return new PintuanFailTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '1953',
                    'keyword_id_list' => [
                        8,
                        1,
                        5,
                    ],
                    'title' => '拼团失败通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'character_string8' => '',
                        'thing1' => '',
                        'thing5' => '',
                    ],
                ],
                'local' => [
                    'name' => '拼团失败通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/pt_fail_notice.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '拼团失败通知(模板编号：AT0141 )',
                    'img_url' => $iconUrlPrefix . 'aliapp/pt_fail_notice.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0301',
                    'keyword_id_list' => [
                        6,
                        2,
                        5,
                    ],
                    'title' => '拼团失败通知',
                ],
                'local' => [
                    'name' => '拼团失败通知(模板编号：BD0301 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/pt_fail_notice.png',
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM417894406',
                    'keyword_id_list' => 'OPENTM417894406',
                    'title' => '拼团失败通知',
                ],
                'local' => [
                    'name' => '拼团失败通知',
                    'img_url' => $iconUrlPrefix . 'wechat/pt_fail_notice.png',
                ],
            ],
            'mobile' => [
                'local' => [
                    'title' => '拼团失败提醒',
                    'content' => '例如：您参与的拼团在规定时间内没有拼团成功，已自动退款。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [],
                    'key' => 'user'
                ]
            ]
        ];
    }
}
