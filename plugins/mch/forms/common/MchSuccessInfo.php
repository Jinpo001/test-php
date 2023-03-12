<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 6:12 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mch\forms\common;

use app\forms\common\template\order_pay_template\BaseInfo;

class MchSuccessInfo extends BaseInfo
{
    public const TPL_NAME = 'mch_order_tpl';
    protected $key = 'mch';
    protected $chineseName = '新订单通知';

    public function getSendClass()
    {
        return new MchOrderTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '4498',
                    'keyword_id_list' => [
                        2,
                        3,
                        4,
                        1,
                    ],
                    'title' => '订单进度提醒',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'character_string2' => '',
                        'amount3' => '',
                        'date4' => '',
                        'thing1' => '',
                    ]
                ],
                'local' => [
                    'name' => '订单进度提醒(类目: 服装/鞋/箱包  )',
                    'img_url' => $iconUrlPrefix . 'wxapp/mch-tpl-2.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单状态通知（模板编号：AT0049 )',
                    'img_url' => $iconUrlPrefix . 'aliapp/mch-tpl-2.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0061',
                    'keyword_id_list' => [
                        6,
                        87,
                        36,
                        8,
                    ],
                    'title' => '新订单通知',
                ],
                'local' => [
                    'name' => '新订单通知（模板编号：BD0061 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/mch-tpl-2.png',
                ],
            ]
        ];
    }
}
