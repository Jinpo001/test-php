<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:29 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;


use app\forms\common\template\tplmsg\ActivitySuccessTemplate;

class ActivitySuccessInfo extends BaseInfo
{
    public const TPL_NAME = 'enroll_success_tpl';
    protected $key = 'store';
    protected $chineseName = '活动状态通知';

    public function getSendClass()
    {
        return new ActivitySuccessTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '1437',
                    'keyword_id_list' => [1, 2, 4],
                    'title' => '活动状态通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing1' => '',
                        'thing2' => '',
                        'thing4' => '',
                    ],
                ],
                'local' => [
                    'name' => '活动状态通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/enroll_success_tpl.png',
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM206215295',
                    'keyword_id_list' => 'OPENTM206215295',
                    'title' => '客户预约提醒',
                ],
                'local' => [
                    'name' => '客户预约提醒',
                    'img_url' => $iconUrlPrefix . 'wechat/check_in_tpl.png',
                ],
            ],
        ];
    }
}