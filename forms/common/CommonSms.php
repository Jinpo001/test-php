<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/4
 * Time: 17:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common;


use app\forms\common\template\order_pay_template\BaseInfo;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\plugins\Plugin;

class CommonSms extends Model
{
    public $mall;

    public static function getCommon($mall = null)
    {
        $model = new self();
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $model->mall = $mall;
        return $model;
    }

    public function getSetting()
    {
        $setting = [
            'order' => [
                'title' => '订单支付提醒设置',
                'content' => '例如：模板内容：您有一条新的订单，订单号：89757，请登录商城后台查看。',
                'support_mch' => true,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您有一个新的订单，订单号：${order}，则只需填写order'
                    ]
                ],
                'key' => 'admin' // 接收方身份 admin--管理员  user--用户
            ],
            'order_refund' => [
                'title' => '订单退款提醒设置',
                'content' => '例如：模板内容：您有一条新的退款订单，请登录商城后台查看。',
                'support_mch' => true,
                'loading' => false,
                'variable' => [],
                'key' => 'admin'
            ],
            'captcha' => [
                'title' => '发送短信验证码设置',
                'content' => '例如：模板内容：您的验证码为89757，请勿告知他人。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'template_variable',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的验证码为${code}，请勿告知他人。，则只需填写code'
                    ]
                ],
                'key' => 'user'
            ],
        ];
        $list = TemplateList::getInstance()->register();
        foreach ($list as $item => $value) {
            /** @var BaseInfo $tplClass */
            $tplClass = new $value();
            $config = $tplClass->config('mobile');
            if (empty($config)) {
                continue;
            }
            $setting[$item] = $config['local'];
        }
        $setting = array_merge($setting, [

            'integral' => [
                'title' => '积分变动提醒',
                'content' => '模板内容：您的积分增加了10，剩余20。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name1',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的积分${name1}了${name2}，剩余${name3}，则只需填写name1'
                    ],
                    [
                        'key' => 'name2',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的积分${name1}了${name2}，剩余${name3}，则只需填写name2'
                    ],
                    [
                        'key' => 'name3',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的积分${name1}了${name2}，剩余${name3}，则只需填写name3'
                    ],
                ],
                'key' => 'user'
            ],
            'balance' => [
                'title' => '余额变动提醒',
                'content' => '模板内容：您的余额增加了10，剩余20。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name1',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的余额${name1}了${name2}，剩余${name3}，则只需填写name1'
                    ],
                    [
                        'key' => 'name2',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的余额${name1}了${name2}，剩余${name3}，则只需填写name2'
                    ],
                    [
                        'key' => 'name3',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：您的余额${name1}了${name2}，剩余${name3}，则只需填写name3'
                    ],
                ],
                'key' => 'user'
            ],
            'brokerage' => [
                'title' => '预计佣金提醒',
                'content' => '模板内容：会员张三下了一个订单， 预计可得100。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name1',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：会员${name}下了一个订单， 预计可得${money}。则只需填写name'
                    ],
                    [
                        'key' => 'name2',
                        'value' => '模板变量',
                        'desc' => '例如：模板内容：会员${name}下了一个订单， 预计可得${money}。则只需填写money'
                    ],
                ],
                'key' => 'user'
            ],
            'tailMoney' => [
                'title' => '商品到货短信通知',
                'content' => '例如：模板内容：您预定的${name}商品已经开购，请尽快支付',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'name',
                        'value' => '模板变量name',
                        'desc' => '例如：模板内容: "您预定的${name}商品已经开购，请尽快支付"，则需填写name',
                    ],
                ],
                'key' => 'user'
            ],
            'pay_password_reset' => [
                'title' => '余额支付密码重置提醒',
                'content' => '模板内容：您的余额支付密码已重置，请及时设置。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [],
                'key' => 'user'
            ],
        ]);
        try {
            $plugins = \Yii::$app->mall->role->pluginList;
            foreach ($plugins as $plugin) {
                if (method_exists($plugin, 'getSmsSetting')) {
                    $setting = array_merge($setting, $plugin->getSmsSetting());
                }
            }
        } catch (\Exception $exception) {
        }
        return $setting;
    }
}
