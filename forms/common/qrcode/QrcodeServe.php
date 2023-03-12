<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/4
 * Time: 10:01 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\qrcode;

use app\models\Model;
use app\plugins\Plugin;

class QrcodeServe extends Model
{
    public function getQrcode($platform, $args = [])
    {
        $platformList = $this->getPlatform();
        if (!isset($platformList[$platform])) {
            throw new \Exception('暂无权限访问');
        }
        if (!method_exists($platformList[$platform], 'getQrcodeServe')) {
            throw new \Exception('平台' . $platform . '暂不支持获取');
        }
        return $platformList[$platform]->getQrcodeServe()->getQrcode($args);
    }

    public function getQrcodeAll($args = [])
    {
        $res = [];
        $platformList = $this->getPlatform();
        foreach ($platformList as $platform => $plugin) {
            if (method_exists($plugin, 'getQrcodeServe')) {
                $res[$platform] = $plugin->getQrcodeServe()->getQrcode($args);
            }
        }
        return $res;
    }

    /**
     * @return Plugin[]
     * 获取可使用平台
     */
    protected function getPlatform()
    {
        $res = [];
        $pluginList = \Yii::$app->mall->role->pluginList;
        foreach ($pluginList as $plugin) {
            if ($plugin->getIsPlatformPlugin()) {
                $res[$plugin->getName()] = $plugin;
            }
        }
        return $res;
    }
}
