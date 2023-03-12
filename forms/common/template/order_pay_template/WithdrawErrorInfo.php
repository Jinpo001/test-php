<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:43 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;


use app\forms\common\template\tplmsg\WithdrawErrorTemplate;

class WithdrawErrorInfo extends BaseInfo
{
    public const TPL_NAME = 'withdraw_error_tpl';
    protected $key = 'share';
    protected $chineseName = '提现失败通知';

    public function getSendClass()
    {
        return new WithdrawErrorTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '3173',
                    'keyword_id_list' => [1, 2],
                    'title' => '提现失败通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'amount1' => '',
                        'name2' => '',
                    ],
                ],
                'local' => [
                    'name' => '提现失败通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/withdraw_error_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '提现失败',
                    'img_url' => $iconUrlPrefix . 'aliapp/withdraw_error_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD1161',
                    'keyword_id_list' => [2, 3],
                    'title' => '提现失败通知'
                ],
                'local' => [
                    'name' => '提现失败(模板编号: BD1161 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/withdraw_error_tpl.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM413491756',
                    'keyword_id_list' => 'OPENTM413491756',
                    'title' => '提现失败提醒'
                ],
                'local' => [
                    'name' => '提现失败提醒',
                    'img_url' => $iconUrlPrefix . 'wechat/withdraw_error_tpl.png'
                ]
            ]
        ];
    }
}