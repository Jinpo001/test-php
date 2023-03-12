<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:46 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;


use app\forms\common\template\tplmsg\RemoveIdentityTemplate;

class RemoveIdentityInfo extends BaseInfo
{
    public const TPL_NAME = 'remove_identity_tpl';
    protected $key = 'share';
    protected $chineseName = '会员等级变更通知';

    public function getSendClass()
    {
        return new RemoveIdentityTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '861',
                    'keyword_id_list' => [3, 2],
                    'title' => '会员等级变更通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing3' => '',
                        'date2' => '',
                    ],
                ],
                'local' => [
                    'name' => '会员等级变更通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/remove_identity_tpl.png',
                ]
            ],
            'aliapp' => [
                'local' => [
                    'name' => '温馨提示',
                    'img_url' => $iconUrlPrefix . 'aliapp/remove_identity_tpl.png'
                ]
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0643',
                    'keyword_id_list' => [5, 3],
                    'title' => '账户变动提醒'
                ],
                'local' => [
                    'name' => '账户变动提醒(模板编号: BD0643 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/remove_identity_tpl.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM406524975',
                    'keyword_id_list' => 'OPENTM406524975',
                    'title' => '等级变更通知'
                ],
                'local' => [
                    'name' => '等级变更通知',
                    'img_url' => $iconUrlPrefix . 'wechat/remove_identity_tpl.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '会员等级变更',
                    'content' => '例如：您好，您的分销商身份已被解除。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：您好，您的${name}身份已被解除。则填写name'
                        ]
                    ],
                    'key' => 'user'
                ],
            ]
        ];
    }
}