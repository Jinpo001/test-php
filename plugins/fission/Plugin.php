<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\fission;

use app\forms\OrderConfig;
use app\helpers\PluginHelper;
use app\plugins\fission\handlers\OrderPayedHandlerClass;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基础设置',
                'route' => 'plugin/fission/mall/setting/index',
                'icon' => 'el-icon-star-on',
            ],
            [
                'name' => '红包墙活动',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/fission/mall/activity/index',
                'action' => [
                    [
                        'name' => '活动编辑',
                        'route' => 'plugin/fission/mall/activity/edit',
                    ],
                ],
            ],
            [
                'name' => '红包记录',
                'route' => 'plugin/fission/mall/log/index',
                'icon' => 'el-icon-star-on',
                'action' => [
                    [
                        'name' => '活动编辑',
                        'route' => 'plugin/fission/mall/log/detail',
                    ],
                ],
            ],
        ];
    }

    public function handler()
    {

    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'fission';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '红包墙';
    }

    public function getAppConfig()
    {
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/image';
        return [
            'app_image' => [
                'banner_image' => $imageBaseUrl . '/banner.jpg',
                'fxhb_none' => $imageBaseUrl . '/fxhb_none.png',
                'bg' => $imageBaseUrl . '/bg.png',
                'share_modal_bg' => $imageBaseUrl . '/share_modal_bg.png',
                'hongbao_bg' => $imageBaseUrl . '/hongbao_bg.png',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/fission/mall/setting/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'fission',
                'name' => '红包墙',
                'open_type' => 'navigate',
                'icon' => $iconBaseUrl . '/icon-fission.png',
                'value' => '/plugins/fission/index/index',
            ],
        ];
    }

    public function getStatisticsMenus($bool = true)
    {
        return [];
    }

    public function goodsAuth()
    {
        return [
            'is_show_and_buy_auth' => false,
            'is_min_number' => false,
            'is_limit_buy' => false,
            'is_setting_send_type' => true,
            'is_time' => false
        ];
    }

    public function getOrderPayedHandleClass()
    {
        return new OrderPayedHandlerClass();
    }

    public function getOrderConfig()
    {
        return new OrderConfig([
            'is_sms' => 1,
            'is_print' => 1,
            'is_mail' => 1,
            'is_share' => 1,
            'support_share' => 1,
        ]);
    }
}
