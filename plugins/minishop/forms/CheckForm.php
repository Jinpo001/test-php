<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/17
 * Time: 5:36 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\plugins\wxapp\Plugin;

class CheckForm extends Model
{
    /**
     * @return Plugin
     * @throws \Exception
     */
    public function check()
    {
        if (!\Yii::$app->plugin->getInstalledPlugin('wxapp')) {
            throw new \Exception('微信小程序插件未安装，请先安装后再使用');
        }
        try {
            /* @var Plugin $plugin */
            $plugin = \Yii::$app->plugin->getPlugin('wxapp');
            $wechat = $plugin->getWechat();
        } catch (\Exception $exception) {
            throw new \Exception('请先配置微信小程序基础配置，请先配置后再使用');
        }
        return $plugin;
    }
}
