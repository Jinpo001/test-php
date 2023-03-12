<?php

namespace app\plugins\teller;

use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\forms\OrderConfig;
use app\forms\PickLinkForm;
use app\forms\mall\pay_type_setting\CommonPayType;
use app\models\PayType;
use app\helpers\PluginHelper;
use app\plugins\teller\forms\WechatServiceScanPay;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\handlers\TellerOrderCanceledHandler;
use app\plugins\teller\handlers\TellerOrderCreatedEventHandler;
use app\plugins\teller\handlers\TellerOrderPayEventHandler;
use app\plugins\teller\handlers\TellerOrderSalesEventHandler;

class Plugin extends \app\plugins\Plugin
{
    public function getMenus()
    {
        return [
            [
                'name' => '基础设置',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/teller/mall/index/index',
            ],
            [
                'name' => '收银员',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/teller/mall/cashier/index',
                'action' => [
                    [
                        'name' => '收银员编辑',
                        'route' => 'plugin/teller/mall/cashier/detail',
                    ],
                ],
            ],
            [
                'name' => '导购员',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/teller/mall/sales/index',
                'action' => [
                    [
                        'name' => '导购员编辑',
                        'route' => 'plugin/teller/mall/sales/detail',
                    ],
                ],
            ],
            [
                'name' => '业绩明细',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/teller/mall/push/index',
            ],
            [
                'name' => '交班记录',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/teller/mall/shifts/index',
                'action' => [
                    [
                        'name' => '交班记录详情',
                        'route' => 'plugin/teller/mall/shifts/show',
                    ],
                ],
            ],
            [
                'name' => '打印设置',
                'icon' => 'el-icon-star-on',
                'route' => 'plugin/teller/mall/printer/index',
            ],
        ];
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'teller';
    }


    public function getDisplayName()
    {
        return '收银台';
    }

    public function getPickLink()
    {
        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/pick-link';

        return [
            [
                'key' => 'teller',
                'name' => '动态付款码',
                'open_type' => '',
                'icon' => $iconBaseUrl . '/icon-teller.png',
                'value' => '/plugins/teller/index/index',
                'ignore' => [PickLinkForm::IGNORE_TITLE],
            ],
        ];
    }

    public function getOrderPayedHandleClass()
    {
        return new TellerOrderPayEventHandler();
    }

    public function getOrderSalesHandleClass()
    {
        return new TellerOrderSalesEventHandler();
    }
    
    public function getOrderCreatedHandleClass()
    {
        return new TellerOrderCreatedEventHandler();
    }

    public function getOrderCanceledHandleClass()
    {
        return new TellerOrderCanceledHandler();
    }

    public function getOrderConfig()
    {
        $setting = (new CommonTellerSetting())->search();
        $config = new OrderConfig([
            'is_sms' => 1,
            'is_mail' => 1,
            'is_print' => 1,
            'is_share' => $setting['is_share'],
            'support_share' => 1,
        ]);

        return $config;
    }

    public function getWechatScanPay()
    {
        $setting = (new CommonTellerSetting())->search();
        $id = $setting['wechat_pay_id'];
        if (!$id) {
            throw new \Exception('请先配置公众号微信支付');
        }
        $payType = PayType::findOne($id);
        $config = [];
        if ($payType->cert_pem && $payType->key_pem) {
            $this->generatePem($config, $payType->cert_pem, $payType->key_pem);
        }

        $this->xWechatPay = new WechatServiceScanPay(array_merge([
            'appId' => $payType->appid,
            'mchId' => $payType->mchid,
            'key' => $payType->key,
        ], $config));

        return $this->xWechatPay;
    }

    public function getAlipayScanPay()
    {
        $payType = $this->getAliPayConfig();
        $aop = new AopClient(
            $payType->alipay_appid,
            AlipayKeyPair::create($payType->app_private_key, $payType->alipay_public_key)
        );
        return $aop;
    }

    public function getAliPayConfig()
    {
        $setting = (new CommonTellerSetting())->search();
        $id = $setting['ali_pay_id'];
        if (!$id) {
            throw new \Exception('请先配置公众号支付宝支付');
        }
        $payType = PayType::findOne($id);

        return $payType;
    }

    /**
     * @param $config
     * @param $cert_pem
     * @param $key_pem
     */
    private function generatePem(&$config, $cert_pem, $key_pem)
    {
        $pemDir = \Yii::$app->runtimePath . '/pem';
        make_dir($pemDir);
        $certPemFile = $pemDir . '/' . md5($cert_pem);
        if (!file_exists($certPemFile)) {
            file_put_contents($certPemFile, $cert_pem);
        }
        $keyPemFile = $pemDir . '/' . md5($key_pem);
        if (!file_exists($keyPemFile)) {
            file_put_contents($keyPemFile, $key_pem);
        }
        $config['certPemFile'] = $certPemFile;
        $config['keyPemFile'] = $keyPemFile;
    }
}
