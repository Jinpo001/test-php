<?php

namespace app\plugins\step\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;


class StepNoticeInfo extends BaseInfo
{
    public const TPL_NAME = 'step_notice';
    protected $key = 'step';
    protected $chineseName = '步数宝--签到提醒';

    public function getSendClass()
    {
        return new StepNoticeTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '817',
                    'keyword_id_list' => [
                        1,
                        3,
                        5,
                    ],
                    'title' => '邀请成功通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'name1' => '',
                        'time3' => '',
                        'thing5' => '',
                    ],
                ],
                'local' => [
                    'name' => '签到提醒(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/check_in_tpl.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '打卡提醒（模板编号：AT0051 )',
                    'img_url' => $iconUrlPrefix . 'aliapp/check_in_tpl.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0243',
                    'keyword_id_list' => [
                        14,
                        1,
                        24,
                    ],
                    'title' => '打卡提醒',
                ],
                'local' => [
                    'name' => '打卡提醒（模板编号：BD0381 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/check_in_tpl.png',
                ],
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM206215295',
                    'keyword_id_list' => 'OPENTM206215295',
                    'title' => '客户预约提醒',
                ],
                'local' => [
                    'name' => '客户预约提醒（步数宝提醒）',
                    'img_url' => $iconUrlPrefix . 'wechat/check_in_tpl.png',
                ],
            ],
            'mobile' => [
                'local' => [
                    'title' => '步数宝--签到提醒',
                    'content' => '例如：亲爱的会员，别忘记今日的签到哦。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [],
                    'key' => 'user'
                ]
            ]
        ];
    }
}
