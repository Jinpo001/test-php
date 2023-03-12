<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/9
 * Time: 5:06 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop;

use app\handlers\HandlerBase;
use app\models\PaymentOrderUnion;
use app\plugins\minishop\forms\PaymentForm;
use app\plugins\minishop\handlers\HandlerRegister;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '交易组件',
                'route' => 'plugin/minishop/mall/index',
                'icon' => 'el-icon-star-on',
            ]
        ];
    }

    public function getName()
    {
        return 'minishop';
    }

    public function getDisplayName()
    {
        return '交易组件';
    }

    public function getIndexRoute()
    {
        return 'plugin/minishop/mall/index';
    }

    public function handler()
    {
        $register = new HandlerRegister();
        $handlerClasses = $register->getHandlers();
        foreach ($handlerClasses as $HandlerClass) {
            $handler = new $HandlerClass();
            if ($handler instanceof HandlerBase) {
                $handler->register();
            }
        }
        return $this;
    }

    /**
     * @param array $config
     * @return array
     */
    public function getPayment($config)
    {
        $form = new PaymentForm();
        $form->paymentOrderUnion = $config['paymentOrderUnion'];
        $form->payData = $config['payData'];
        return $form->add();
    }
}
