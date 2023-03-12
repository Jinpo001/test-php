<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/16 16:27
 */


namespace app\core\payment;

use Alipay\AlipayRequestFactory;
use Alipay\AopClient;
use app\forms\common\CertSN;
use app\forms\common\CommonOption;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\refund\BaseRefund;
use app\forms\common\transfer\BaseTransfer;
use app\forms\mall\recharge\RechargeSettingForm;
use app\helpers\ArrayHelper;
use app\models\Option;
use app\models\Order;
use app\models\PaymentOrderUnion;
use app\models\PaymentRefund;
use app\models\QrCodeParameter;
use app\models\User;
use app\plugins\teller\jobs\OrderQueryJob;
use app\plugins\ttapp\forms\pay\TtPay;
use app\plugins\wxapp\forms\Enum;
use app\plugins\wxapp\forms\WechatServicePay;
use luweiss\Wechat\WechatPay;
use yii\base\Component;

class Payment extends Component
{
    const PAY_TYPE_HUODAO = 'huodao';
    const PAY_TYPE_BALANCE = 'balance';
    const PAY_TYPE_WECHAT = 'wechat';
    const PAY_TYPE_ALIPAY = 'alipay';
    const PAY_TYPE_BAIDU = 'baidu';
    const PAY_TYPE_TOUTIAO = 'toutiao';
    const PAY_TYPE_WECHAT_H5 = 'wechat_h5';
    const PAY_TYPE_ALIPAY_H5 = 'alipay_h5';
    const PAY_TYPE_UNIONPAY = 'unionpay';

    const PAY_TYPE_WECHAT_SCAN = 'wechat_scan';
    const PAY_TYPE_ALIPAY_SCAN = 'alipay_scan';
    const PAY_TYPE_POS = 'pos'; // pos机支付
    const PAY_TYPE_CASH = 'cash'; // 现金

    const PAY_BALANCE_TYPE_PASSWORD = 'pay_password'; // 余额支付密码
    const PAY_BALANCE_TYPE_QR_CODE = 'pay_code'; // 会员付款码

    /**
     * @param PaymentOrder|PaymentOrder[] $paymentOrders 支付订单数据，支持单个或多个订单
     * @return int
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function createOrder($paymentOrders, $user = null)
    {
        $user = $user ?: $this->getUser();

        if (!is_array($paymentOrders)) {
            if ($paymentOrders instanceof PaymentOrder) {
                $paymentOrders = [$paymentOrders];
            } else {
                throw new PaymentException("`$paymentOrders`不是有效的PaymentOrder对象。");
            }
        }
        if (!count($paymentOrders)) {
            throw new PaymentException("`$paymentOrders`不能为空。");
        }
        $orderNos = [];
        $amount = 0;
        $title = '';
        foreach ($paymentOrders as $paymentOrder) {
            $orderNos[] = $paymentOrder->orderNo;
            $amount = $amount + $paymentOrder->amount;
            $title = $title . str_replace(';', '', filter_emoji($paymentOrder->title)) . ';';
        }
        sort($orderNos);
        $orderNos[] = $amount;
        $unionOrderNo = 'HM' . mb_substr(md5(json_encode($orderNos)), 2);
        $title = mb_substr($title, 0, 32);
        $appVersion = \Yii::$app->getAppVersion();
        $paymentOrderUnion = new PaymentOrderUnion();
        $paymentOrderUnion->mall_id = \Yii::$app->mall->id;
        $paymentOrderUnion->user_id = $user->id;
        $paymentOrderUnion->order_no = $unionOrderNo;
        $paymentOrderUnion->amount = $amount;
        $paymentOrderUnion->title = $title;
        $paymentOrderUnion->platform = \Yii::$app->appPlatform;
        if ($appVersion) {
            $paymentOrderUnion->app_version = $appVersion;
        }
        foreach ($paymentOrders as $paymentOrder) {
            $supportPayTypes = $paymentOrder->supportPayTypes;
            if ($supportPayTypes) {
                $supportPayTypes = (array)$supportPayTypes;
            }
        }
        if (!empty($supportPayTypes) && is_array($supportPayTypes)) { // 支付方式含online_pay时分成微信支付和支付宝支付
            $appendPayTypes = [];
            foreach ($supportPayTypes as $index => $payType) {
                if ($payType == 'online_pay') {
                    $appendPayTypes[] = static::PAY_TYPE_WECHAT;
                    $appendPayTypes[] = static::PAY_TYPE_ALIPAY;
                    $appendPayTypes[] = static::PAY_TYPE_BAIDU;
                    $appendPayTypes[] = static::PAY_TYPE_TOUTIAO;
                    $appendPayTypes[] = static::PAY_TYPE_WECHAT_H5;
                    $appendPayTypes[] = static::PAY_TYPE_ALIPAY_H5;
//                    $appendPayTypes[] = static::PAY_TYPE_UNIONPAY;
                    unset($supportPayTypes[$index]);
                    break;
                }
            }
            $supportPayTypes = array_merge($supportPayTypes, $appendPayTypes);
        }

        $paymentOrderUnion->support_pay_types = $paymentOrderUnion->encodeSupportPayTypes($supportPayTypes);
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$paymentOrderUnion->save()) {
                throw new PaymentException();
            }
            foreach ($paymentOrders as $paymentOrder) {
                $model = new \app\models\PaymentOrder();
                $model->payment_order_union_id = $paymentOrderUnion->id;
                $model->order_no = $paymentOrder->orderNo;
                $model->amount = $paymentOrder->amount;
                $model->title = $paymentOrder->title;
                $model->notify_class = $paymentOrder->notifyClass;
                if (!$model->save()) {
                    throw new PaymentException();
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return $paymentOrderUnion->id;
    }

    public function getPayData($id, $payType, $url = '')
    {
        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }

        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        if (!empty($supportPayTypes)
            && is_array($supportPayTypes)
            && !in_array($payType, $supportPayTypes)) {
            if ($paymentOrderUnion->amount != 0) { // 订单金额为0时使用余额支付
                throw new PaymentException('该订单不支持此支付方式。');
            }
        }

        /** @var User $user */
        $user = $this->getUser();
        switch ($payType) {
            case static::PAY_TYPE_HUODAO:
                $data = [
                    'pay_type' => $payType,
                    'id' => $paymentOrderUnion->id,
                ];
                break;
            case static::PAY_TYPE_BALANCE:
                $data = [
                    'pay_type' => $payType,
                    'id' => $paymentOrderUnion->id,
                    'balance_amount' => price_format(\Yii::$app->currency->setUser($user)->balance->select()),
                    'order_amount' => $paymentOrderUnion->amount,
                ];
                break;
            case static::PAY_TYPE_WECHAT:
                $plugin = \Yii::$app->plugin->getPlugin('wxapp');
                /** @var WechatPay $wechatPay */
                $wechatPay = $plugin->getWechatPay(Enum::WECHAT_PAY_SERVICE);
                if (get_class($wechatPay) == 'app\\plugins\\wxapp\\forms\\WechatServicePay') {
                    $params['sub_openid'] = $user->username;
                    $appid = $wechatPay->sub_appid;
                } else {
                    $params['openid'] = $user->username;
                    $appid = $wechatPay->appId;
                }
                $res = $wechatPay->unifiedOrder(array_merge([
                    'body' => $paymentOrderUnion->title,
                    'out_trade_no' => $paymentOrderUnion->order_no,
                    'total_fee' => $paymentOrderUnion->amount * 100,
                    'notify_url' => $this->getNotifyUrl('wechat.php'),
                    'trade_type' => WechatPay::TRADE_TYPE_JSAPI,
                ], $params));
                $appPayData = [
                    'appId' => $appid,
                    'timeStamp' => (string)time(),
                    'nonceStr' => md5(uniqid()),
                    'package' => 'prepay_id=' . $res['prepay_id'],
                    'signType' => WechatPay::SIGN_TYPE_MD5,
                ];
                $appPayData['paySign'] = $wechatPay->makeSign($appPayData);
                try {
                    if (\Yii::$app->plugin->getInstalledPlugin('minishop')) {
                        /* @var \app\plugins\minishop\Plugin $plugin */
                        $plugin = \Yii::$app->plugin->getPlugin('minishop');
                        $orderInfo = $plugin->getPayment([
                            'paymentOrderUnion' => $paymentOrderUnion,
                            'payData' => [
                                'prepay_id' => $res['prepay_id']
                            ]
                        ]);
                        if (!empty($orderInfo)) {
                            $appPayData['orderInfo'] = $orderInfo;
                        }
                    }
                } catch (\Exception $exception) {
                }
                $data = array_merge([
                    'pay_type' => $payType,
                    'id' => $paymentOrderUnion->id,
                ], $appPayData);
                break;
            case static::PAY_TYPE_ALIPAY:
            case static::PAY_TYPE_ALIPAY_H5:
                $file = 'alipay.php';
                if (\Yii::$app->appPlatform == 'wechat') {
                    $file = 'alipay-mp.php';
                } elseif (\Yii::$app->appPlatform == 'mobile') {
                    $file = 'alipay-mobile.php';
                }
                $config = [
                    'notify_url' => $this->getNotifyUrl($file),
                    'biz_content' => [
                        'subject' => rtrim($paymentOrderUnion->title, ';'),
                        'out_trade_no' => $paymentOrderUnion->order_no,
                        'total_amount' => $paymentOrderUnion->amount,
                    ],
                ];
                $platform = \Yii::$app->appPlatform;
                $plugin = \Yii::$app->plugin->getPlugin($platform);
                if (\Yii::$app->appPlatform == 'aliapp') {
                    $aliappConfig = $plugin->getAliConfig();
                    $aop = $plugin->getAliAopClient();
                } else {
                    $aliappConfig = $plugin->getAliConfig($platform);
                    $aop = $plugin->getAliAopClient($platform);
                }
                $aliappConfig = ArrayHelper::toArray($aliappConfig);
                /** @var AopClient $aop */
                if ($platform == 'aliapp') {
                    $method = 'alipay.trade.create';
                    $config['biz_content']['buyer_id'] = $user->username;
                } else {
                    $method = 'alipay.trade.wap.pay';
                    $config['return_url'] = $url;
                    $config['app_cert_sn'] = CertSN::getSn($aliappConfig['appcert']);
                    $config['alipay_root_cert_sn'] = CertSN::getSn($aliappConfig['alipay_rootcert'], true);
                }
                $request = AlipayRequestFactory::create($method, $config);
                if ($platform == 'aliapp') {
                    $resData = $aop->execute($request)->getData();
                    $data = [
                        'tradeNO' => $resData['trade_no'],
                    ];
                } else {
                    $resData = $aop->pageExecuteUrl($request);
                    $data = [
                        'url' => $resData
                    ];
                }
                break;
            case static::PAY_TYPE_BAIDU:
                $plugin = \Yii::$app->plugin->getPlugin('bdapp');
                /** @var \app\plugins\bdapp\forms\BdappPaymentForm $paymentForm */
                $paymentForm = $plugin->getPaymentForm();
                $data = $paymentForm->getAppPayData([
                    'title' => $paymentOrderUnion->title,
                    'order_no' => $paymentOrderUnion->order_no,
                    'amount' => $paymentOrderUnion->amount,
                    'username' => $user->username,
                ]);
                break;
            case static::PAY_TYPE_TOUTIAO:
                $plugin = \Yii::$app->plugin->getPlugin('ttapp');
                /** @var TtPay $ttPay */
                $ttPay = $plugin->getTtPay();
                $res = $ttPay->genData([
                    'out_order_no' => $paymentOrderUnion->order_no,
                    'uid' => $user->username,
                    'total_amount' => price_format($paymentOrderUnion->amount * 100, 'float', 2),
                    'alipay_amount' => $paymentOrderUnion->amount,
                    'currency' => 'CNY',
                    'subject' => str_replace("&", '', rtrim($paymentOrderUnion->title, ';')),
                    'body' => str_replace("&", '', rtrim($paymentOrderUnion->title, ';')),
                    'notify_url' => $this->getNotifyUrl('toutiao.php'),
                ]);

                $data = $res;
                break;
            case static::PAY_TYPE_WECHAT_H5:
                if (!in_array(\Yii::$app->appPlatform, ['mobile', 'wechat'])) {
                    throw new PaymentException('支付信息错误。');
                }
                $plugin = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform);
                /** @var WechatPay $wechatPay */
                $wechatPay = $plugin->getWechatPay(\Yii::$app->appPlatform);
                $params = [];
                $file = 'wechat.php';
                if (\Yii::$app->appPlatform == 'wechat') {
                    $openids = PlatformConfig::instance()->getPlatformOpenid($user);
                    if (count($openids) == 2) {
                        $openid =  $openids['wechat'];
                    } else {
                        $openid = $user->username;
                    }
                    $file = 'wechat-mp.php';
                    if (
                        get_class($wechatPay) == 'app\\plugins\\mobile\\forms\\WechatServicePay'
                        || get_class($wechatPay) == 'app\\plugins\\wechat\\forms\\WechatServicePay'
                    ) {
                        $params['sub_openid'] = $openid;
                        $appid = $wechatPay->sub_appid;
                    } else {
                        $params['openid'] = $openid;
                        $appid = $wechatPay->appId;
                    }
                } elseif (\Yii::$app->appPlatform == 'mobile') {
                    $file = 'wechat-mobile.php';
                    $params['spbill_create_ip'] = get_client_ip();
                    $params['trade_type'] = WechatPay::TRADE_TYPE_MWEB;
                    $params['scene_info'] = json_encode([
                        'h5_info' => [
                            'type' => 'Wap',
                            'wap_url' => \Yii::$app->request->hostInfo,
                            'wap_name' => \Yii::$app->mall->name
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                }
                $res = $wechatPay->unifiedOrder(array_merge([
                    'body' => $paymentOrderUnion->title,
                    'out_trade_no' => $paymentOrderUnion->order_no,
                    'total_fee' => $paymentOrderUnion->amount * 100,
                    'notify_url' => $this->getNotifyUrl($file),
                    'trade_type' => WechatPay::TRADE_TYPE_JSAPI,
                ], $params));
                $appPayData = [];
                if (isset($appid)) {
                    $appPayData['appId'] = $appid;
                }
                $appPayData = array_merge($appPayData, [
                    'timeStamp' => (string)time(),
                    'nonceStr' => md5(uniqid()),
                    'package' => 'prepay_id=' . $res['prepay_id'],
                    'signType' => WechatPay::SIGN_TYPE_MD5,
                ]);
                $appPayData['paySign'] = $wechatPay->makeSign($appPayData);
                if (\Yii::$app->appPlatform == 'mobile') {
                    \Yii::error('==========mweb_url===========');
                    \Yii::error($url);
                    $url = $url ? $res['mweb_url'] . '&redirect_url=' . urlencode($url) : $res['mweb_url'];
                    $appPayData['mweb_url'] = $url;
                    \Yii::error($appPayData['mweb_url']);
                }
                $data = array_merge([
                    'pay_type' => $payType,
                    'id' => $paymentOrderUnion->id,
                ], $appPayData);
                break;
            case static::PAY_TYPE_WECHAT_SCAN:
                if (!$this->auth_code) {
                    throw new PaymentException('请传入付款码');
                }
                if (!in_array(\Yii::$app->appPlatform, ['webapp'])) {
                    throw new PaymentException('支付信息错误。');
                }

                \Yii::$app->queue->delay(0)->push(new OrderQueryJob([
                    'id' => $paymentOrderUnion->id,
                    'pay_type' => self::PAY_TYPE_WECHAT_SCAN,
                ]));

                \Yii::$app->setAppPlatform('teller');
                $plugin = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform);
                $wechatPay = $plugin->getWechatScanPay();
                $payData = $wechatPay->micropayOrder([
                    'nonce_str' => md5(uniqid()),
                    'body' => $paymentOrderUnion->title,
                    'out_trade_no' => $paymentOrderUnion->order_no,
                    'total_fee' => $paymentOrderUnion->amount * 100,
                    'auth_code' => $this->auth_code,
                ]);
                
                $data = array_merge([
                    'id' => $paymentOrderUnion->id
                ], $payData);
                break;
            case static::PAY_TYPE_ALIPAY_SCAN:
                if (!$this->auth_code) {
                    throw new PaymentException('请传入付款码');
                }
                if (!in_array(\Yii::$app->appPlatform, ['webapp'])) {
                    throw new PaymentException('支付信息错误。');
                }

                \Yii::$app->queue->delay(0)->push(new OrderQueryJob([
                    'id' => $paymentOrderUnion->id,
                    'pay_type' => self::PAY_TYPE_ALIPAY_SCAN,
                ]));

                \Yii::$app->setAppPlatform('teller');
                $plugin = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform);
                $aop = $plugin->getAlipayScanPay();

                $payType = $plugin->getAliPayConfig();
                $request = AlipayRequestFactory::create('alipay.trade.pay', [
                    'biz_content' => [
                        'out_trade_no' => $paymentOrderUnion->order_no,
                        'scene' => 'bar_code',
                        'auth_code' => $this->auth_code,
                        'subject' => rtrim($paymentOrderUnion->title, ';'),
                        'total_amount' => $paymentOrderUnion->amount,
                    ],
                    'app_cert_sn' => CertSN::getSn($payType->appcert),
                    'alipay_root_cert_sn' => CertSN::getSn($payType->alipay_rootcert, true)
                ]);

                $payData = $aop->execute($request)->getData();
                
                $data = array_merge([
                    'id' => $paymentOrderUnion->id
                ], $payData);
                break;
            default:
                throw new PaymentException('未知的`payType`。');
                break;
        }
        return $data;
    }

    /**
     * @param $id
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function payBuyBalance($id, $pay_number = null, $params = [])
    {
        if ($this->getIsGuest()) {
            throw new PaymentException('用户未登录。');
        }
        $user = $this->getUser();
        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }
        if (intval($paymentOrderUnion->is_pay) === 1) {
            throw new PaymentException('订单已支付。');
        }
        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        if (!empty($supportPayTypes)
            && is_array($supportPayTypes)
            && !in_array(static::PAY_TYPE_BALANCE, $supportPayTypes)) {
            if ($paymentOrderUnion->amount != 0) { // 订单金额为0时可以使用余额支付
                throw new PaymentException('暂不支持余额支付。');
            }
        }

        $isVerify = $this->isVerifyPayPassword();

        $userInfo = $user->userInfo;
        if (!$userInfo->pay_password && isset($params['is_need_pay_password']) && $params['is_need_pay_password'] == 0) {
            $isVerify = false;
        }
        // 验证支付密码
        if ($isVerify && $paymentOrderUnion->amount > 0) {
            if (!$userInfo->pay_password) {
                throw new PaymentException('会员未设置支付密码');
            }

            if (!$pay_number) {
                throw new PaymentException('请输入支付密码');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($pay_number, $userInfo->pay_password)){
                throw new \Exception('支付密码错误');
            }
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var \app\models\PaymentOrder[] $paymentOrders */
            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();
            $totalAmount = 0;
            foreach ($paymentOrders as $paymentOrder) {
                $totalAmount += $paymentOrder->amount;
            }
            $balanceAmount = \Yii::$app->currency->setUser($user)->balance->select();
            if ($balanceAmount < $totalAmount) {
                throw new PaymentException('账户余额不足。');
            }
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 3;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }

            // 余额付款码支付需要记录付款码使用情况
            $parameter = QrCodeParameter::find()->andWhere(['token' => $pay_number])->one();
            if ($parameter) {
                $data = json_decode($parameter->data, true);
                $data['payment_order_id'] = $paymentOrderUnion->id;
                $parameter->use_number = 1;
                $parameter->data = json_encode($data);
                if (!$parameter->save()) {
                    throw new \Exception($parameter->getFirstErrors());
                }
            }

            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 3;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                $NotifyClass = $paymentOrder->notify_class;
                /** @var PaymentNotify $notifyObject */
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => static::PAY_TYPE_BALANCE,
                ]);
                if ($po->amount > 0) {
                    $customDesc = \Yii::$app->serializer->encode(['order_no' => $paymentOrder->order_no]);
                    if (!\Yii::$app->currency->setUser($user)->balance
                        ->sub($po->amount, '账户余额支付：' . $po->amount . '元', $customDesc, $paymentOrder->order_no)) {
                        throw new PaymentException('余额操作失败。');
                    }
                }
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $exception) {
                    \Yii::error($exception->getMessage());
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return true;
    }

    // 是否开启支付密码验证
    public function isVerifyPayPassword()
    {
        $rechargeForm = new RechargeSettingForm();
        $rechargeSetting = $rechargeForm->setting();
        
        return $rechargeSetting['is_pay_password'] ? true : false;
    }

    /**
     * 现金支付
     * @param $id
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function payBuyCash($id)
    {
        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }
        if (intval($paymentOrderUnion->is_pay) === 1) {
            throw new PaymentException('订单已支付。');
        }
        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        if (!empty($supportPayTypes)
            && is_array($supportPayTypes)
            && !in_array(static::PAY_TYPE_CASH, $supportPayTypes)) {
            if ($paymentOrderUnion->amount != 0) { // 订单金额为0时可以使用现金支付
                throw new PaymentException('暂不支持现金支付。');
            }
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var \app\models\PaymentOrder[] $paymentOrders */
            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();

            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 9;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 9;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                $NotifyClass = $paymentOrder->notify_class;
                /** @var PaymentNotify $notifyObject */
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => static::PAY_TYPE_CASH,
                ]);
                
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $exception) {
                    \Yii::error($exception->getMessage());
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return true;
    }

    /**
     * 现金支付
     * @param $id
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function payBuyPos($id)
    {
        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }
        if (intval($paymentOrderUnion->is_pay) === 1) {
            throw new PaymentException('订单已支付。');
        }
        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        if (!empty($supportPayTypes)
            && is_array($supportPayTypes)
            && !in_array(static::PAY_TYPE_POS, $supportPayTypes)) {
            if ($paymentOrderUnion->amount != 0) { // 订单金额为0时可以使用现金支付
                throw new PaymentException('暂不支持Pos支付。');
            }
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var \app\models\PaymentOrder[] $paymentOrders */
            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();

            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 10;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 10;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                $NotifyClass = $paymentOrder->notify_class;
                /** @var PaymentNotify $notifyObject */
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => static::PAY_TYPE_POS,
                ]);
                
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $exception) {
                    \Yii::error($exception->getMessage());
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function payBuyHuodao($id)
    {
        if ($this->getIsGuest()) {
            throw new PaymentException('用户未登录。');
        }

        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }
        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        if (!empty($supportPayTypes)
            && is_array($supportPayTypes)
            && !in_array(static::PAY_TYPE_HUODAO, $supportPayTypes)) {
            throw new PaymentException('暂不支持货到付款。');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $paymentOrderUnion->is_pay = 0;
            $paymentOrderUnion->pay_type = 2;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            /** @var \app\models\PaymentOrder[] $paymentOrders */
            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();
            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay = 0;
                $paymentOrder->pay_type = 2;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                $NotifyClass = $paymentOrder->notify_class;
                /** @var PaymentNotify $notifyObject */
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => static::PAY_TYPE_HUODAO,
                ]);
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $exception) {
                    \Yii::error($exception->getMessage());
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return true;
    }


    /**
     * 扫付款码支付
     * @param $id
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function payBuyScan($id)
    {
        if ($this->getIsGuest()) {
            throw new PaymentException('用户未登录。');
        }
        $user = $this->getUser();
        $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
        if (!$paymentOrderUnion) {
            throw new PaymentException('待支付订单不存在。');
        }
        if (intval($paymentOrderUnion->is_pay) === 1) {
            throw new PaymentException('订单已支付。');
        }
        $supportPayTypes = (array)$paymentOrderUnion->decodeSupportPayTypes($paymentOrderUnion->support_pay_types);
        if (!empty($supportPayTypes)
            && is_array($supportPayTypes)
            && !in_array(static::PAY_TYPE_BALANCE, $supportPayTypes)) {
            if ($paymentOrderUnion->amount != 0) { // 订单金额为0时可以使用余额支付
                throw new PaymentException('暂不支持余额支付。');
            }
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var \app\models\PaymentOrder[] $paymentOrders */
            $paymentOrders = \app\models\PaymentOrder::find()
                ->where(['payment_order_union_id' => $paymentOrderUnion->id,])
                ->all();
            $totalAmount = 0;
            foreach ($paymentOrders as $paymentOrder) {
                $totalAmount += $paymentOrder->amount;
            }
            $balanceAmount = \Yii::$app->currency->setUser($user)->balance->select();
            if ($balanceAmount < $totalAmount) {
                throw new PaymentException('账户余额不足。');
            }
            $paymentOrderUnion->is_pay = 1;
            $paymentOrderUnion->pay_type = 3;
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($paymentOrderUnion->getFirstErrors());
            }
            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->is_pay = 1;
                $paymentOrder->pay_type = 3;
                if (!$paymentOrder->save()) {
                    throw new \Exception($paymentOrder->getFirstErrors());
                }
                $NotifyClass = $paymentOrder->notify_class;
                /** @var PaymentNotify $notifyObject */
                $notifyObject = new $NotifyClass();
                $po = new PaymentOrder([
                    'orderNo' => $paymentOrder->order_no,
                    'amount' => (float)$paymentOrder->amount,
                    'title' => $paymentOrder->title,
                    'notifyClass' => $paymentOrder->notify_class,
                    'payType' => static::PAY_TYPE_BALANCE,
                ]);
                if ($po->amount > 0) {
                    $customDesc = \Yii::$app->serializer->encode(['order_no' => $paymentOrder->order_no]);
                    if (!\Yii::$app->currency->setUser($user)->balance
                        ->sub($po->amount, '账户余额支付：' . $po->amount . '元', $customDesc, $paymentOrder->order_no)) {
                        throw new PaymentException('余额操作失败。');
                    }
                }
                try {
                    $notifyObject->notify($po);
                } catch (\Exception $exception) {
                    \Yii::error($exception->getMessage());
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            throw $e;
        }
        return true;
    }

    private function getNotifyUrl($file)
    {
        $protocol = env('PAY_NOTIFY_PROTOCOL');
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/pay-notify/' . $file;
        if ($protocol) {
            $url = str_replace('http://', ($protocol . '://'), $url);
            $url = str_replace('https://', ($protocol . '://'), $url);
        }
        return $url;
    }

    /**
     * @param integer|array $condition payment_order_union_id or ['order_no' => 'order_no']
     * @return PaymentOrderUnion|null
     */
    public function getPaymentOrderUnion($condition)
    {
        return PaymentOrderUnion::findOne($condition);
    }

    /**
     * @param $paymentOrderUnionId
     * @return array|\app\models\PaymentOrder[]
     */
    public function getPaymentOrders($paymentOrderUnionId)
    {
        return \app\models\PaymentOrder::find()->where([
            'payment_order_union_id' => $paymentOrderUnionId,
        ])->all();
    }

    /**
     * @param string $orderNo 订单号
     * @param double $price 退款金额
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function refund($orderNo, $price)
    {
        $paymentOrder = \app\models\PaymentOrder::findOne([
            'order_no' => $orderNo,
            'is_pay' => 1
        ]);
        if (!$paymentOrder) {
            throw new PaymentException('无效的订单号');
        }

        if (price_format($paymentOrder->amount - $paymentOrder->refund) < price_format($price)) {
            throw new PaymentException('退款金额大于可退款金额');
        }

        $paymentOrderUnion = $this->getPaymentOrderUnion(['id' => $paymentOrder->payment_order_union_id]);

        $t = \Yii::$app->db->beginTransaction();
        $newOrderNo = Order::getOrderNo('HM');
        try {
            $paymentRefund = new PaymentRefund();
            $paymentRefund->mall_id = $paymentOrderUnion->mall_id;
            $paymentRefund->user_id = $paymentOrderUnion->user_id;
            $paymentRefund->amount = $price;
            $paymentRefund->order_no = 'HM' . substr(md5($paymentOrder->order_no), 6) . substr($newOrderNo, -4);
            $paymentRefund->is_pay = 0;
            $paymentRefund->pay_type = 0;
            $paymentRefund->title = "订单退款:{$orderNo}";
            $paymentRefund->created_at = mysql_timestamp();
            $paymentRefund->out_trade_no = $paymentOrderUnion->order_no;

            $class = $this->refundClass($paymentOrderUnion->pay_type);
            if ($class->refund($paymentRefund, $paymentOrderUnion)) {
                $paymentOrder->refund += $price;
                if (!$paymentOrder->save()) {
                    throw new PaymentException();
                }
                $t->commit();
                return true;
            } else {
                throw new PaymentException();
            }
        } catch (PaymentException $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * @param $payType
     * @return BaseRefund
     * @throws PaymentException
     */
    private function refundClass($payType)
    {
        switch ($payType) {
            case 1:
                $class = 'app\\plugins\\wxapp\\forms\\WxRefund';
                break;
            case 2:
                $class = 'app\\forms\\common\\refund\\HuodaoRefund';
                break;
            case 3:
                $class = 'app\\forms\\common\\refund\\BalanceRefund';
                break;
            case 4:
                $class = 'app\\plugins\\aliapp\\forms\\AlipayRefund';
                break;
            case 5:
                $class = 'app\\plugins\\bdapp\\forms\\BdRefund';
                break;
            case 6:
                $class = 'app\\plugins\\ttapp\\forms\\TtRefund';
                break;
            case 7:
                $class = 'app\\forms\\common\\refund\\WxH5Refund';
                break;
            case 8:
                $class = 'app\\forms\\common\\refund\\AliH5Refund';
                break;
            case 9:
                $class = 'app\\plugins\\teller\\forms\\CashRefund';
                break;
            case 10:
                $class = 'app\\plugins\\teller\\forms\\PosRefund';
                break;
            case 11:
                $class = 'app\\plugins\\teller\\forms\\WechatScanRefund';
                break;
            case 12:
                $class = 'app\\plugins\\teller\\forms\\AliScanRefund';
                break;
            default:
                throw new PaymentException('无效的支付方式');
        }

        if (!class_exists($class)) {
            throw new PaymentException('未安装相关平台的插件或未知的客户端平台，平台标识`');
        }

        return new $class();
    }

    /**
     * @param PaymentTransfer $paymentTransfer
     * @return bool
     * @throws PaymentException
     * @throws \yii\db\Exception
     */
    public function transfer($paymentTransfer)
    {
        if (!$paymentTransfer instanceof PaymentTransfer) {
            throw new PaymentException('无效的参数，参数不是有效的app\\core\\payment\\PaymentTransfer对象');
        }

        $model = \app\models\PaymentTransfer::find()->where([
            'order_no' => $paymentTransfer->orderNo, 'user_id' => $paymentTransfer->user->id,
            'mall_id' => $paymentTransfer->user->mall_id
        ])->one();

        if (!$model) {
            $model = new \app\models\PaymentTransfer();
            $model->transfer_order_no = $paymentTransfer->orderNo;
            $model->mall_id = $paymentTransfer->user->mall_id;
            $model->user_id = $paymentTransfer->user->id;
            $model->title = $paymentTransfer->title;
            $model->amount = $paymentTransfer->amount;
            $model->pay_type = $paymentTransfer->transferType;
            $model->is_pay = 0;
            $model->order_no = 'HM' . substr(md5($paymentTransfer->orderNo), 2);
            $model->created_at = mysql_timestamp();
        }

        if ($model->is_pay == 1) {
            throw new PaymentException('该订单号已经打款，请勿重复操作');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            $class = $this->transferClass($model->pay_type);
            if ($class->transfer($model, $paymentTransfer->user)) {
                $t->commit();
                return true;
            } else {
                throw new PaymentException();
            }
        } catch (PaymentException $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * @param $type
     * @return BaseTransfer
     * @throws PaymentException
     */
    private function transferClass($type)
    {
        switch ($type) {
            case PaymentTransfer::TRANSFER_TYPE_WECHAT:
                $class = 'app\\plugins\\wxapp\\forms\\WechatTransfer';
                break;
            case PaymentTransfer::TRANSFER_TYPE_ALIPAY:
                $class = 'app\\plugins\\aliapp\\forms\\AlipayTransfer';
                break;
            case PaymentTransfer::TRANSFER_TYPE_BAIDU:
                $class = 'app\\plugins\\bdapp\\forms\\BdTransfer';
                break;
            case PaymentTransfer::TRANSFER_TYPE_TOUTIAO:
                $class = 'app\\plugins\\ttapp\\forms\\TtTransfer';
                break;
            case PaymentTransfer::TRANSFER_TYPE_WECHAT_MP:
                $class = 'app\\forms\\common\\transfer\\WxH5Transfer';
                break;
            default:
                throw new PaymentException('无效的方式');
        }

        if (!class_exists($class)) {
            throw new PaymentException('未安装相关平台的插件或未知的客户端平台，平台标识`');
        }

        return new $class();
    }

    /**
     * 余额功能是否开启
     * @return bool
     */
    public function isRechargeOpen()
    {
        $default = [
            'status' => '0',
            'type' => '0',
            'bj_pic_url' => '',
            'ad_pic_url' => '',
            'page_url' => '',
            're_pic_url' => '',
            'explain' => '',
        ];
        $setting = CommonOption::get(Option::NAME_RECHARGE_SETTING, \Yii::$app->mall->id, Option::GROUP_APP, $default);
        return $setting['status'] == 1 ? true : false;
    }

    public function getUser()
    {
        return User::findOne(\Yii::$app->user->id);
    }

    public function getIsGuest()
    {
        return \Yii::$app->user->isGuest;
    }
}
