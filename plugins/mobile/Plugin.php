<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\mobile;

use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\forms\mall\pay_type_setting\CommonPayType;
use app\helpers\PluginHelper;
use app\models\PayType;
use app\plugins\mobile\forms\common\QrcodeServe;
use app\plugins\mobile\forms\common\TemplateSend;
use app\plugins\mobile\forms\mall\IndexForm;
use app\plugins\mobile\forms\WechatServicePay;
use luweiss\Wechat\WechatPay;

class Plugin extends \app\plugins\Plugin
{
    public $wechat;
    private $xWechatPay;

    public function afterInstall()
    {
        $form = new IndexForm();
        return $form->zip();
    }

    public function afterUpdate()
    {
        $form = new IndexForm();
        return $form->zip();
    }

    public function getMenus()
    {
        return [
            [
                'name' => '基础配置',
                'route' => 'plugin/mobile/mall/config/index',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '其他设置',
                'route' => 'plugin/mobile/mall/config/register',
                'icon' => 'el-icon-setting',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/mobile/mall/config/index';
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'mobile';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return 'h5商城';
    }

    public function getHeaderNav()
    {
        return [
            'name' => 'h5商城',
            'url' => \Yii::$app->urlManager->createUrl(['plugin/mobile/mall/config/index']),
            'new_window' => true,
        ];
    }

    public function getIsPlatformPlugin()
    {
        return true;
    }

    public function templateList()
    {
        return [];
    }

    public function filePath()
    {
        if (is_we7()) {
            return '/plugins/mobile/we_h5';
        } else {
            return '/plugins/mobile/h5';
        }
    }

    public function getWebUri()
    {
        $baseUrl = \Yii::$app->request->baseUrl;
        $list = explode('/', $baseUrl);
        if (array_pop($list) == 'pay-notify') {
            $rootUrl = rtrim(dirname(rtrim(dirname($baseUrl), '/')), '/');
        } else {
            $rootUrl = rtrim(dirname($baseUrl), '/');
        }
        $apiRoot = str_replace('http://', 'https://', \Yii::$app->request->hostInfo);
        return $apiRoot . $rootUrl . $this->filePath() . '/mall/' . \Yii::$app->mall->id . '/?#/';
    }

    /**
     * @param $platform
     * @return AopClient
     * @throws \Exception
     */
    public function getAliAopClient($platform)
    {
        $aliappConfig = $this->getAliConfig($platform);
        $aop = new AopClient(
            $aliappConfig->alipay_appid,
            AlipayKeyPair::create($aliappConfig->app_private_key, $aliappConfig->alipay_public_key)
        );
        return $aop;
    }

    public function getAliConfig($platform)
    {
        $payId = (CommonPayType::get($platform))['ali'];
        $payType = PayType::findOne($payId);
        if (!$payType) {
            throw new \Exception('支付宝支付尚未配置。');
        }
        return $payType;
    }

    public function checkSign($platform)
    {
        $config = $this->getAliConfig($platform);
        if (!$config || !$config->alipay_public_key || !$config->app_private_key || !$config->alipay_appid) {
            throw new \Exception('H5商城支付宝支付尚未配置。');
        }
        try {
            $passed = $this->getAliAopClient($platform)->verify();
        } catch (\Exception $ex) {
            $passed = null;
            printf('%s | %s' . PHP_EOL, get_class($ex), $ex->getMessage()); // 验证过程发生错误，打印异常信息
            \Yii::error($ex->getMessage());
        }

        return $passed;
    }

    public function getWechatPay($platform)
    {
        if ($this->xWechatPay) {
            return $this->xWechatPay;
        }
        $newPayType = (CommonPayType::get($platform))['wx'];
        if (!$newPayType) {
            throw new \Exception('支付方式不存在');
        }
        $payType = PayType::findOne($newPayType);
        $config = [];
        if ($payType->is_service) {
            if ($payType->service_cert_pem && $payType->service_key_pem) {
                $this->generatePem($config, $payType->service_cert_pem, $payType->service_key_pem);
            }
            $this->xWechatPay = new WechatServicePay(array_merge([
                'appId' => $payType->service_appid,
                'mchId' => $payType->service_mchid,
                'key' => $payType->service_key,
                'sub_appid' =>  $payType->appid,
                'sub_mch_id' => $payType->mchid
            ], $config));
        } else {
            if ($payType->cert_pem && $payType->key_pem) {
                $this->generatePem($config, $payType->cert_pem, $payType->key_pem);
            }
            $this->xWechatPay = new WechatPay(array_merge([
                'appId' => $payType->appid,
                'mchId' => $payType->mchid,
                'key' => $payType->key,
            ], $config));
        }
        return $this->xWechatPay;
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

    public function templateSender()
    {
        return new TemplateSend();
    }

    // 获取平台图标
    public function getPlatformIconUrl()
    {
        return [
            [
                'key' => $this->getName(),
                'name' => $this->getDisplayName(),
                'icon' => PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/mobile.png'
            ]
        ];
    }

    /**
     * @param array $config
     * @return QrcodeServe
     */
    public function getQrcodeServe($config = [])
    {
        return new QrcodeServe($config);
    }

    public function getNotSupport()
    {
        return [
            'navbar' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
                '/pages/binding/binding',
            ],
            'home_nav' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
                '/pages/binding/binding',
            ],
            'user_center' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
                '/pages/binding/binding',
            ],
        ];
    }

    public function getSmsSetting()
    {
        return [
            'password' => [
                'title' => '注册成功短信--仅限h5用户',
                'content' => '例如：您好，恭喜您注册成功，您的默认密码为123456，请及时登录并修改密码。',
                'support_mch' => false,
                'loading' => false,
                'variable' => [
                    [
                        'key' => 'password',
                        'value' => '模板变量',
                        'desc' => '例如：您好，恭喜您注册成功，您的默认密码为${password}，请及时登录并修改密码。，则只需填写password'
                    ]
                ],
                'key' => 'user'
            ],
        ];
    }
}
