<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/2
 * Time: 17:53
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\teller\forms\common;

use app\forms\common\prints\templates\FirstTemplate;


/**
 * Class FirstTemplate
 * @package app\forms\common\prints\templates
 */
class TellerOrderTemplate extends FirstTemplate
{
    public function getContentByArray()
    {
        $data = $this->data;

        $show_type = \yii\helpers\BaseJson::decode($this->printer->show_type);
        switch ($this->printer->big) {
            case 1:
                $otherHandle = 'b';
                break;
            case 2:
                $otherHandle = 'dB';
                break;
            default:
                $otherHandle = 'bR';
                break;
        }
        $content = [
            [
                'handle' => 'times',
                'content' => ''
            ],
            [
                'handle' => 'centerBold',
                'content' => $data->mall_name
            ],
            [
                'handle' => 'bR',
                'content' => '订单类型：' . $data->order_type
            ],
            [
                'handle' => 'bR',
                'content' => '支付方式：' . $data->pay_type
            ],
            [
                'handle' => 'bR',
                'content' => '订单号：' . $data->order_no
            ],
            [
                'handle' => 'bR',
                'content' => '下单时间：' . $data->created_at
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
        ];

        foreach ($data->new_goods_list as $info) {
            $content[] = [
                'handle' => 'TableNoAttr',
                'content' => array_merge($info, ['is_goods_no' => $show_type['goods_no']]),
                'show' => $show_type['attr'] == 0 ? 1 : 0
            ];

            $content[] = [
                'handle' => 'TableAttr',
                'content' => array_merge($info, ['is_goods_no' => $show_type['goods_no']]),
                'show' => $show_type['attr'] == 1 ? 1 : 0
            ];

            if (!empty($info['form_data']) && $show_type['form_data'] == 1) {
                $content[] = [
                    'handle' => 'divide',
                    'content' => ''
                ];

                if ($info['form_name']) {
                    $content[] = [
                        'handle' => 'bR',
                        'content' => $info['form_name'],
                    ];
                }

                foreach ($info['form_data'] as $form) {
                    //todo img_upload 存在 Undefined index value;
                    if (!isset($form['value'])) {
                        continue;
                    }
                    if ($form['value'] && in_array($form['key'], ['text', 'textarea', 'radio', 'date', 'time'])) {
                        $content[] = [
                            'handle' => 'bR',
                            'content' => $form['label'] . '：' . $form['value']
                        ];
                    }

                    if ($form['value'] && in_array($form['key'], ['checkbox'])) {
                        $content[] = [
                            'handle' => 'bR',
                            'content' => $form['label'] . '：' . implode(',', $form['value'])
                        ];
                    }
                }
            }
            $content[] = [
                'handle' => 'divide',
                'content' => ''
            ];
        }

        $content3 = [
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->total_goods_original_price
                    ]
                ],
                'content' => '商品总计：'
            ],
            [
                'handle' => 'bR',
                'content' => '商品数量：' . array_sum(array_column($data->goods_list, 'num'))
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->express_price
                    ]
                ],
                'content' => '运费：',
                'show' => $data->send_type != 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->coupon_discount_price
                    ]
                ],
                'content' => '优惠券优惠：',
                'show' => $data->coupon_discount_price ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->integral_deduction_price
                    ]
                ],
                'content' => '积分抵扣：',
                'show' => $data->integral_deduction_price ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->member_discount_price
                    ]
                ],
                'content' => '会员优惠：',
                'show' => $data->member_discount_price ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => abs($data->back_price)
                    ]
                ],
                'content' => '后台改价：',
                'show' => $data->back_price != 0 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->erase_price
                    ]
                ],
                'content' => '抹零：',
                'show' => $data->erase_price != 0 ? 1 : 0
            ],
        ];

        $content = array_merge($content, $content3);

        if (count($data->plugin_data) > 0) {
            foreach ($data->plugin_data as $datum) {
                $content[] = [
                    'handle' => 'bR',
                    'content' => $datum['label'] . '：' . $datum['value'],
                    'show' => 1
                ];
            }
        }
        $content2 = [
            [
                'handle' => 'bR',
                'children' => [
                    [
                        'handle' => 'price',
                        'content' => $data->total_pay_price
                    ]
                ],
                'content' => '实际支付：'
            ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            // [
            //     'handle' => $otherHandle,
            //     'content' => ($data->send_type != 1 ? '收货人：' : '联系人：') . $data->name
            // ],
            // [
            //     'handle' => $otherHandle,
            //     'content' => '收货地址：' . $data->address,
            //     'show' => $data->send_type != 1 ? 1 : 0
            // ],
            // [
            //     'handle' => $otherHandle,
            //     'content' => ($data->send_type != 1 ? '收货人电话：' : '联系人电话：') . $data->mobile
            // ],
            [
                'handle' => 'divide',
                'content' => ''
            ],
            [
                'handle' => 'bR',
                'content' => '门店信息',
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'content' => $data->store_name,
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'content' => $data->store_mobile,
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'bR',
                'content' => $data->store_address,
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'divide',
                'content' => '',
                'show' => $data->send_type == 1 ? 1 : 0
            ],
            [
                'handle' => 'remarkText',
                'content' => '备注：' . $data->remark,
                'show' => $data->remark ? 1 : 0
            ],
        ];
        $content = array_merge($content, $content2);

        return $content;
    }
}
