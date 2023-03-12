<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:39 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;


use app\forms\common\template\tplmsg\WithdrawSuccessTemplate;

class WithdrawSuccessInfo extends BaseInfo
{
    public const TPL_NAME = 'withdraw_success_tpl';
    protected $key = 'share';
    protected $chineseName = '提现成功通知';

    public function getSendClass()
    {
        return new WithdrawSuccessTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '2001',
                    'keyword_id_list' => [1, 2, 3, 4],
                    'title' => '提现成功通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'amount1' => '',
                        'amount2' => '',
                        'thing3' => '',
                        'thing4' => '',
                    ],
                ],
                'local' => [
                    'name' => '提现成功通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/withdraw_success_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '提现成功',
                    'img_url' => $iconUrlPrefix . 'aliapp/withdraw_success_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0781',
                    'keyword_id_list' => [5, 3, 6, 7],
                    'title' => '提现到账通知'
                ],
                'local' => [
                    'name' => '提现成功(模板编号: BD0781 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/withdraw_success_tpl.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM207422808',
                    'keyword_id_list' => 'OPENTM207422808',
                    'title' => '提现通知'
                ],
                'local' => [
                    'name' => '提现成功通知',
                    'img_url' => $iconUrlPrefix . 'wechat/withdraw_success_tpl.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '提现提醒',
                    'content' => '例如：您好，您申请的提现已审核通过。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：您好，您申请的提现已审核${name}。则填写name'
                        ]
                    ],
                    'key' => 'user'
                ],
            ]
        ];
    }
}