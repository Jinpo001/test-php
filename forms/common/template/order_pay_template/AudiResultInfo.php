<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 4:33 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\AudiResultTemplate;

class AudiResultInfo extends BaseInfo
{
    public const TPL_NAME = 'audit_result_tpl';
    protected $key = 'store';
    protected $chineseName = '审核结果通知';

    public function getSendClass()
    {
        return new AudiResultTemplate();
    }

    public function configAll()
    {
        $iconUrlPrefix = './statics/img/mall/tplmsg/';
        return [
            'wxapp' => [
                'config' => [
                    'id' => '818',
                    'keyword_id_list' => [4, 2, 1, 3],
                    'title' => '审核结果通知',
                    'categoryId' => '307', // 类目id
                    'type' => 2, // 订阅类型 2--一次性订阅 1--永久订阅
                    'data' => [
                        'thing4' => '',
                        'phrase2' => '',
                        'thing1' => '',
                        'time3' => '',
                    ],
                ],
                'local' => [
                    'name' => '审核结果通知(类目: 服装/鞋/箱包 )',
                    'img_url' => $iconUrlPrefix . 'wxapp/mch-tpl-1.png',
                ]
            ],
            'aliapp' => [
                'name' => '审核结果通知',
                'img_url' => $iconUrlPrefix . 'aliapp/mch-tpl-1.png'
            ],
            'bdapp' => [
                'config' => [
                    'id' => 'BD0641',
                    'keyword_id_list' => [1, 2, 6, 4],
                    'title' => '审核状态通知'
                ],
                'local' => [
                    'name' => '审核结果通知(模板编号: BD0141 )',
                    'img_url' => $iconUrlPrefix . 'bdapp/mch-tpl-1.png'
                ]
            ],
            'ttapp' => [
                'local' => [
                    'name' => '审核通知',
                    'img_url' => $iconUrlPrefix . 'ttapp/mch-tpl-1.png'
                ]
            ],
            'wechat' => [
                'config' => [
                    'id' => 'OPENTM415281152',
                    'keyword_id_list' => 'OPENTM415281152',
                    'title' => '审核结果通知'
                ],
                'local' => [
                    'name' => '审核结果通知',
                    'img_url' => $iconUrlPrefix . 'wechat/mch-tpl-1.png'
                ]
            ],
            'mobile' => [
                'local' => [
                    'title' => '审核结果通知',
                    'content' => '例如：您好，您的分销商身份审核已通过。',
                    'support_mch' => false,
                    'loading' => false,
                    'variable' => [
                        [
                            'key' => 'name',
                            'value' => '模板变量',
                            'desc' => '例如：您好，您的${name}身份审核${results}。则填写name'
                        ],
                        [
                            'key' => 'results',
                            'value' => '模板变量',
                            'desc' => '例如：您好，您的${name}身份审核${results}。则填写results'
                        ],
                    ],
                    'key' => 'user'
                ],
            ]
        ];
    }
}