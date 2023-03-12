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
use app\plugins\pintuan\forms\common\PintuanSuccessTemplate;

class PintuanSuccessInfo extends BaseInfo
{
    public const TPL_NAME = 'pintuan_success_notice';
    protected $key = 'pintuan';
    protected $chineseName = '拼团成功通知';

    public function getSendClass()
    {
        return new PintuanSuccessTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '980',
                    'keyword_id_list' => [
                        1,
                        3,
                        5,
                    ],
                    'title' => '拼团成功通知',
                    'categoryId' => '307',
                    'type' => 2,
                    'data' => [
                        'thing1' => '',
                        'number3' => '',
                        'thing5' => '',
                    ],
                ],
                'local' => [
                    'name' => '拼团成功通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/pt_success_notice.png',
                ],
            ],
            'aliapp' => [
                'local' => [
                    'name' => '拼团进度通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/pt_success_notice.png',
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0041',
                    'keyword_id_list' => [
                        6,
                        13,
                        15,
                    ],
                    'title' => '拼团成功通知',
                ],
                'local' => [
                    'name' => '拼团成功通知(模板编号：BD0041 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/pt_success_notice.png',
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM415699251',
                    'keyword_id_list' => 'OPENTM415699251',
                    'title' => '拼团成功通知',
                ],
                'local' => [
                    'name' => '拼团成功通知',
                    'img_url' => $iconUrlPrefix . 'wechat/pt_success_notice.png',
                ],
            ],

        ];
    }
}
