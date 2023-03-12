<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/27
 * Time: 9:40
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template;

use app\forms\common\template\order_pay_template\ActivitySuccessInfo;
use app\forms\common\template\order_pay_template\AudiResultInfo;
use app\forms\common\template\order_pay_template\BaseInfo;
use app\forms\common\template\order_pay_template\OrderCancelInfo;
use app\forms\common\template\order_pay_template\OrderPayInfo;
use app\forms\common\template\order_pay_template\OrderRefundInfo;
use app\forms\common\template\order_pay_template\OrderSendInfo;
use app\forms\common\template\order_pay_template\RemoveIdentityInfo;
use app\forms\common\template\order_pay_template\ShareAudiInfo;
use app\forms\common\template\order_pay_template\TailMoneyInfo;
use app\forms\common\template\order_pay_template\WithdrawErrorInfo;
use app\forms\common\template\order_pay_template\WithdrawSuccessInfo;
use app\models\Mall;
use app\models\Model;
use app\plugins\Plugin;
use yii\helpers\ArrayHelper;

/**
 * Class TemplateList
 * @package app\forms\common\template
 * @property Mall $mall
 * 模板消息列表
 */
class TemplateList extends Model
{
    public static $instance;
    public $mall;
    public $platform;

    public static function getInstance($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        if (!self::$instance) {
            self::$instance = new self([
                'mall' => $mall
            ]);
        }
        return self::$instance;
    }

    /**
     * @param $platform
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     * 获取指定平台的模板消息列表
     */
    public function getList($platform)
    {
        $plugin = \Yii::$app->plugin->getPlugin($platform);
        $list = [];
        if (method_exists($plugin, 'getTemplateList')) {
            $list = $plugin->getTemplateList('tpl_name,tpl_id');
        }
        return $list;
    }

    /**
     * @param string $platform
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \Exception
     * 获取指定平台的模板消息列表--测试模板消息发送
     */
    public function getTestTemplateList($platform)
    {
        $list = $this->getList($platform);
        $newList = [];
        foreach ($list as $item) {
            if (!$item['tpl_id']) {
                continue;
            }
            try {
                $item = ArrayHelper::toArray($item);
                $item['name'] = $this->getTemplateClass($item['tpl_name'])->getChineseName();
                $newList[] = $item;
            } catch (\Exception $exception) {
                continue;
            }
        }
        return $newList;
    }

    /**
     * @param $platform
     * @param array|string $tpl
     * @return array
     * 获取指定平台指定模板消息--前端获取订阅消息发送权限
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    public function getTemplate($platform, $tpl)
    {
        if (is_string($tpl)) {
            $tpl = [$tpl];
        }
        if (!is_array($tpl)) {
            throw new \Exception('tpl参数必须是数组或字符串');
        }
        $list = $this->getList($platform);
        $newList = [];
        foreach ($list as $item) {
            if (in_array($item['tpl_name'], $tpl) && $item['tpl_id']) {
                $newList[] = $item['tpl_id'];
            }
        }
        return $newList;
    }

    /**
     * @return array|string[]
     * 获取所有注册的模板消息
     */
    public function register()
    {
        $list = [
            OrderPayInfo::TPL_NAME => OrderPayInfo::class,
//            ShareAudiInfo::TPL_NAME => ShareAudiInfo::class,
            OrderCancelInfo::TPL_NAME => OrderCancelInfo::class,
            OrderSendInfo::TPL_NAME => OrderSendInfo::class,
            OrderRefundInfo::TPL_NAME => OrderRefundInfo::class,
            ActivitySuccessInfo::TPL_NAME => ActivitySuccessInfo::class,
            AudiResultInfo::TPL_NAME => AudiResultInfo::class,
            WithdrawSuccessInfo::TPL_NAME => WithdrawSuccessInfo::class,
            WithdrawErrorInfo::TPL_NAME => WithdrawErrorInfo::class,
            RemoveIdentityInfo::TPL_NAME => RemoveIdentityInfo::class,
            TailMoneyInfo::TPL_NAME => TailMoneyInfo::class
        ];
        $pluginList = \Yii::$app->mall->role->pluginList;
        foreach ($pluginList as $plugin) {
            $list = array_merge($list, $plugin->templateRegister());
        }
        return $list;
    }

    /**
     * @param string $tpl
     * @return BaseInfo|mixed
     * @throws \Exception
     * 获取指定模板消息对象
     */
    public function getTemplateClass($tpl)
    {
        $list = $this->register();
        if (!isset($list[$tpl])) {
            throw new \Exception('未支持的模板消息');
        }
        return new $list[$tpl]();
    }

    /**
     * @return Plugin[]
     * 获取可使用平台
     */
    public function getPlatform()
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

    /**
     * @param false $add
     * @param string $platformAdd
     * @return array
     * 获取所有模板消息
     */
    public function getTemplateList($add = false, $platformAdd = 'wxapp')
    {
        $platformList = $this->getPlatform();
        // 部分平台的模板消息不显示在小程序消息中
        foreach ($platformList as $key => $plugin) {
            if (in_array($plugin->getName(), ['mobile', 'wechat', 'ttapp'])) {
                unset($platformList[$key]);
            }
        }
        return $this->getTemplateData($platformList, $add, $platformAdd);
    }

    /**
     * @param string $platform
     * @param false $add
     * 获取指定平台的模板消息
     */
    public function getTemplateByPlatform($platform, $add = false)
    {
        $platformList = $this->getPlatform();
        if (!isset($platformList[$platform])) {
            throw new \Exception('没有访问权限');
        }
        return $this->getTemplateData([$platform => $platformList[$platform]], $add, $platform);
    }

    protected function getTemplateData($platformList = [], $add = false, $platformAdd = 'wxapp')
    {
        $newList = [];
        $register = $this->register();
        $tplLocal = []; // 获取模板消息本地配置
        $tplConfig = []; // 获取模板消息一键配置
        foreach ($register as $key => $tpl) {
            /** @var BaseInfo $tplClass */
            $tplClass = new $tpl();
            $configAll = $tplClass->configAll();
            foreach ($configAll as $platform => $config) {
                if (empty($config)) {
                    continue;
                }
                if (!in_array($platform, array_keys($platformList))) {
                    continue;
                }
                $tplName = $tpl::TPL_NAME;
                if (isset($config['local'])) {
                    $tplLocal[$platform][$tplName] = [
                        'name' => $config['local']['name'],
                        'img_url' => $config['local']['img_url'],
                        'tpl_name' => $tplName,
                        $tplName => '',
                        'key' => $tplClass->getKey()
                    ];
                }
                if (isset($config['config'])) {
                    $tplConfig[$platform][$tplName] = $config['config'];
                }
            }
        }
        /** @var Plugin[] $platformList */
        foreach ($platformList as $platform => $plugin) {
            if (!isset($tplLocal[$platform])) {
                continue;
            }
            if ($add && $platformAdd == $platform && method_exists($plugin, 'addTemplate')) {
                // 注：此接口暂时只支持微信,百度
                try {
                    $list = $plugin->addTemplate($tplConfig[$platform]); // 获取指定平台模板消息一键配置
                } catch (\Exception $exception) {
                    \Yii::warning('--一键添加出错--');
                    \Yii::warning($exception);
                    $list = $this->getList($platform); // 获取指定平台模板消息的本地配置
                }
            } else {
                $list = $this->getList($platform); // 获取指定平台模板消息的本地配置
            }
            $tplList = array_column($list, 'tpl_id', 'tpl_name');
            foreach ($tplList as $tplId => $tplName) {
                if (isset($tplLocal[$platform][$tplId][$tplId])) {
                    $tplLocal[$platform][$tplId][$tplId] = $tplName;
                }
            }
            $newList[$platform] = [
                'key' => $platform,
                'name' => $plugin->getDisplayName(),
                'list' => array_values($tplLocal[$platform])
            ];
        }
        return array_values($newList);
    }
}
