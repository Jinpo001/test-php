<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:42
 */


namespace app\plugins\wechat;

use Alipay\AopClient;
use Alipay\Key\AlipayKeyPair;
use app\forms\common\CommonOption;
use app\forms\common\wechat\WechatFactory;
use app\forms\mall\pay_type_setting\CommonPayType;
use app\helpers\PluginHelper;
use app\models\PayType;
use app\models\UserInfo;
use app\models\UserPlatform;
use app\models\WechatWxmpprograms;
use app\plugins\wechat\forms\api\WechatForm;
use app\plugins\wechat\forms\common\QrcodeServe;
use app\plugins\wechat\forms\common\TemplateSend;
use app\plugins\wechat\forms\common\wechat\WechatMenu;
use app\plugins\wechat\forms\common\wechat\WechatSubscribe;
use app\plugins\wechat\forms\common\wechat\WechatTemplate;
use app\plugins\wechat\forms\mall\IndexForm;
use app\plugins\wechat\forms\mall\OtherForm;
use app\plugins\wechat\forms\mall\WechatTemplateForm;
use app\plugins\wechat\forms\WechatServicePay;
use luweiss\Wechat\WechatPay;

class Plugin extends \app\plugins\Plugin
{
    public $wechat;
    private $xWechatPay;
    private $wechatTemplate;

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
                'name' => '基础设置',
                'route' => 'plugin/wechat/mall/config/setting',
                'icon' => 'el-icon-setting',
            ],
            [
                'name' => '其他设置',
                'route' => 'plugin/wechat/mall/config/other',
                'icon' => 'el-icon-setting',
            ],
        ];
    }

    public function getIndexRoute()
    {
        return 'plugin/wechat/mall/config/setting';
    }

    /**
     * 插件唯一id，小写英文开头，仅限小写英文、数字、下划线
     * @return string
     */
    public function getName()
    {
        return 'wechat';
    }

    /**
     * 插件显示名称
     * @return string
     */
    public function getDisplayName()
    {
        return '公众号商城';
    }

    public function getHeaderNav()
    {
        return [
            'name' => '公众号商城',
            'url' => \Yii::$app->urlManager->createUrl(['plugin/wechat/mall/config/setting']),
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


    /**
     * @return \luweiss\Wechat\Wechat
     * @throws \luweiss\Wechat\WechatException
     */
    public function getWechat()
    {
        if ($this->wechat) {
            return $this->wechat;
        }
        $this->wechat = WechatFactory::create('wechat_plugin')->wechat;
        return $this->wechat;
    }

    /**
     * @param  bool
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken($refresh = false)
    {
        return $this->getWechat()->getAccessToken($refresh);
    }

    public function filePath()
    {
        if (is_we7()) {
            return '/plugins/wechat/we_h5';
        } else {
            return '/plugins/wechat/h5';
        }
    }

    public function getWebUri()
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            $baseUrl = \Yii::$app->request->baseUrl;
            $hostInfo = \Yii::$app->request->hostInfo;
        } else {
            $baseUrl = \Yii::$app->baseUrl;
            $hostInfo = \Yii::$app->hostInfo;
        }
        $list = explode('/', $baseUrl);
        if (array_pop($list) == 'pay-notify') {
            $rootUrl = rtrim(dirname(rtrim(dirname($baseUrl), '/')), '/');
        } else {
            $rootUrl = rtrim(dirname($baseUrl), '/');
        }
        $apiRoot = str_replace('http://', 'https://', $hostInfo);
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
            throw new \Exception('公众号商城支付宝支付尚未配置。');
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
        $third = WechatWxmpprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'version' => 2]);
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
                'sub_appid' => $third ? $third->authorizer_appid : $payType->appid,
                'sub_mch_id' => $payType->mchid
            ], $config));
        } else {
            if ($payType->cert_pem && $payType->key_pem) {
                $this->generatePem($config, $payType->cert_pem, $payType->key_pem);
            }
            $this->xWechatPay = new WechatPay(array_merge([
                'appId' => $third ? $third->authorizer_appid : $payType->appid,
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
                'icon' => PluginHelper::getPluginBaseAssetsUrl($this->getName()) . '/img/wechat.png'
            ]
        ];
    }

    public function getWechatTemplate()
    {
        $this->wechatTemplate = new WechatTemplate([
            'accessToken' => $this->getAccessToken()
        ]);
        return $this->wechatTemplate;
    }

    public function addTemplate($templateList)
    {
        $model = new WechatTemplateForm();
        return $model->addTemplate($templateList);
    }

    public function addTemplateList($attributes)
    {
        $form = new WechatTemplateForm();
        return $form->addTemplateList($attributes);
    }

    /**
     * @param string|array $param
     * @return array|\yii\db\ActiveRecord[]|\app\plugins\wechat\models\WechatTemplate[]
     * 获取所有订阅消息
     */
    public function getTemplateList($param = '*')
    {
        $model = new WechatTemplateForm();

        return $model->getTemplateList($param);
    }

    /**
     * @param array $config
     * @return QrcodeServe
     */
    public function getQrcodeServe($config = [])
    {
        return new QrcodeServe($config);
    }

    public function getUserInfo($user)
    {
        try {
            $info = $this->getWechat()->getInfo();
            $name = $info['name'];
            $logo = $info['logo'];
            $qrcode = $info['qrcode'];
        } catch (\Exception $exception) {
            $name = '';
            $logo = '';
            $qrcode = '';
        }
        return [
            'subscribe' => UserPlatform::find()
                ->where(['mall_id' => $user->mall_id, 'user_id' => $user->id, 'platform' => UserInfo::PLATFORM_WECHAT])
            ->select('subscribe')->scalar(),
            'other_config' => CommonOption::get('other_config', \Yii::$app->mall->id, 'wechat', []),
            'wechat_name' => $name,
            'wechat_logo' => $logo,
            'qrcode' => $qrcode,
        ];
    }

    public function getNotSupport()
    {
        return [
            'navbar' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
            'home_nav' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
            'user_center' => [
                '/plugins/step/index/index',
                '/pages/live/index',
                'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin',
            ],
        ];
    }

    public function getOther()
    {
        return (new OtherForm())->config();
    }

    public function updateSubscribe()
    {
        return (new WechatForm())->updateSubscribe();
    }

    public $wechatSubscribe;

    /**
     * @return WechatSubscribe
     * @throws \Exception
     * 获取订阅通知处理类
     */
    public function getWechatSubscribe()
    {
        $this->wechatSubscribe = new WechatSubscribe([
            'accessToken' => $this->getAccessToken()
        ]);
        return $this->wechatSubscribe;
    }

    /**
     * @return WechatMenu
     * @throws \Exception
     * 获取自定义菜单处理类
     */
    public function getWechatMenu()
    {
        return new WechatMenu([
            'accessToken' => $this->getAccessToken()
        ]);
    }
}
