<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 6:12 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;
use app\plugins\bargain\forms\common\BargainFailTemplate;

class BargainFailInfo extends BaseInfo
{
    public const TPL_NAME = 'bargain_fail_tpl';
    protected $key = 'bargain';
    protected $chineseName = '砍价失败通知';

    public function getSendClass()
    {
        return new BargainFailTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '1976',
                    'keyword_id_list' => [
                        2,
                        3,
                        4,
                        5,
                    ],
                    'title' => '砍价失败通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'thing2' => '',
                        'amount3' => '',
                        'amount4' => '',
                        'thing5' => '',
                    ],
                ],
                'local' => [
                    'name' => '砍价失败通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/bargain_fail_tpl.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单状态通知(模板编号：AT0056)',
                    'img_url' => $iconUrlPrefix . 'aliapp/bargain_success_tpl.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD1502',
                    'keyword_id_list' => [
                        1,
                        9,
                        2,
                        6,
                    ],
                    'title' => '砍价成功通知',
                ],
                'local' => [
                    'name' => '砍价失败通知(模板编号：BD1502)',
                    'img_url' => $iconUrlPrefix . 'bdapp/bargain_fail_tpl.png',
                ],
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM417894430',
                    'keyword_id_list' => 'OPENTM417894430',
                    'title' => '砍价失败通知',
                ],
                'local' => [
                    'name' => '砍价失败通知',
                    'img_url' => $iconUrlPrefix . 'wechat/bargain_fail_tpl.png',
                ],
            ],
            'mobile' => [
                'local' => [
                    'title' => '砍价失败提醒',
                    'content' => '例如：您好，您发起的砍价已失败，请重新发起。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [],
                    'key' => 'user'
                ]
            ]
        ];
    }
}
