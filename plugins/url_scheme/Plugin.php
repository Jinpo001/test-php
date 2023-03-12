<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\url_scheme;

use app\helpers\PluginHelper;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '链接生成工具',
                'route' => 'plugin/url_scheme/mall/index',
                'icon' => 'el-icon-star-on',
            ]
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
        return 'url_scheme';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '微信链接生成工具';
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
        return 'plugin/url_scheme/mall/index';
    }

    /**
     * 插件小程序端链接
     * @return array
     */
    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/image/pick-link';

        return [
        ];
    }

    public function getHomePage($type)
    {

    }

    public function getStatisticsMenus($bool = true)
    {
        return [];
    }
}
