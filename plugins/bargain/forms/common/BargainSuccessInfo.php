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
use app\plugins\bargain\forms\common\BargainSuccessTemplate;

class BargainSuccessInfo extends BaseInfo
{
    public const TPL_NAME = 'bargain_success_tpl';
    protected $key = 'bargain';
    protected $chineseName = '砍价成功通知';

    public function getSendClass()
    {
        return new BargainSuccessTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '1975',
                    'keyword_id_list' => [2, 3, 4, 5],
                    'title' => '砍价成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing2' => '',
                        'amount3' => '',
                        'amount4' => '',
                        'thing5' => '',
                    ]
                ],
                'local' => [
                    'name' => '砍价成功通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/bargain_success_tpl.png'
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '订单状态通知',
                    'img_url' => $iconUrlPrefix . 'aliapp/bargain_success_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD1101',
                    'keyword_id_list' => [1, 17, 6, 3],
                    'title' => '砍价成功通知'
                ],
                'local' => [
                    'name' => '砍价成功通知(模板编号：BD1101)',
                    'img_url' => $iconUrlPrefix . 'bdapp/bargain_success_tpl.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM418130463',
                    'keyword_id_list' => 'OPENTM418130463',
                    'title' => '砍价成功通知',
                ],
                'local' => [
                    'name' => '砍价成功通知',
                    'img_url' => $iconUrlPrefix . 'wechat/bargain_success_tpl.png',
                ],
            ],
            'mobile' => [
                'local' => [
                    'title' => '砍价成功提醒',
                    'content' => '例如：您发起的砍价已成功，请及时付款下单。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [],
                    'key' => 'user'
                ]
            ]
        ];
    }
}
