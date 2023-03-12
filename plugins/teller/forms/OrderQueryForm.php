<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms;

use Alipay\AlipayRequestFactory;
use app\core\payment\Payment;
use app\core\response\ApiCode;
use app\forms\common\CertSN;
use app\models\Mall;
use app\models\Model;
use app\models\PaymentOrderUnion;
use app\plugins\teller\Plugin;

class OrderQueryForm extends Model
{
    public $id;// union_id
    public $pay_type;

    public function rules()
    {
        return [
            [['id', 'pay_type'], 'required'],
            [['id'], 'integer'],
            [['pay_type'], 'string']
        ];
    }

    // 订单支付情况查询
    public function getQueryOrder()
    {
        $paymentOrderUnion = PaymentOrderUnion::findOne([
            'id' => $this->id
        ]);

        if (!$paymentOrderUnion) {
            throw new \Exception('订单不存在');
        }

        $data = [
            'pay_status' => 0,//0.未支付 1.已支付 2.取消支付
        ];

        \Yii::$app->setAppPlatform('teller');
        \Yii::$app->setMall(Mall::findOne($paymentOrderUnion->mall_id));
        $plugin = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform);
        switch ($this->pay_type) {
            // 微信
            case Payment::PAY_TYPE_WECHAT_SCAN:
                $wechatPay = $plugin->getWechatScanPay();
                $result = $wechatPay->orderQuery([
                    'nonce_str' => md5(uniqid()),
                    'out_trade_no' => $paymentOrderUnion->order_no,
                ]);

                // 取消支付
                if ($result['trade_state'] == 'PAYERROR') {
                    $data['pay_status'] == 2;
                }
                // 已支付
                if ($result['trade_state'] == 'SUCCESS') {
                    $data['pay_status'] = 1;
                }
                break;
            // 支付宝
            case Payment::PAY_TYPE_ALIPAY_SCAN:
                $aop = $plugin->getAlipayScanPay();
                $payType = $plugin->getAliPayConfig();
                $request = AlipayRequestFactory::create('alipay.trade.query', [
                    'biz_content' => [
                        'out_trade_no' => $paymentOrderUnion->order_no,
                    ],
                    'app_cert_sn' => CertSN::getSn($payType->appcert),
                    'alipay_root_cert_sn' => CertSN::getSn($payType->alipay_rootcert, true)
                ]);

                $result = $aop->execute($request)->getData();

                // 交易支付成功
                if ($result['code'] == 10000 && $result['trade_status'] == 'TRADE_SUCCESS') {
                    $data['pay_status'] = 1;
                }
                // 未付款交易超时关闭，或支付完成后全额退款
                if ($result['code'] == 10000 && $result['trade_status'] == 'TRADE_CLOSED') {
                    $data['pay_status'] = 2;
                }

                break;
            default:
                throw new \Exception('付款码支付方式异常');
                break;
        }

        return array_merge($result, $data);
    }

    // 订单撤销
    public function reverse()
    {
        try {
            $paymentOrderUnion = PaymentOrderUnion::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id
            ]);

            if (!$paymentOrderUnion) {
                throw new \Exception('订单不存在');
            }

            \Yii::$app->setAppPlatform('teller');
            $plugin = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform);

            switch ($this->pay_type) {
                case Payment::PAY_TYPE_WECHAT_SCAN:
                    $wechatPay = $plugin->getWechatScanPay(\Yii::$app->appPlatform);
                    $res = $wechatPay->payReverse([
                        'nonce_str' => md5(uniqid()),
                        'out_trade_no' => $paymentOrderUnion->order_no,
                        'appid' => $wechatPay->appId,
                        'mch_id' => $wechatPay->mchId
                    ]);

                    \Yii::warning($res);
                    if ($res['return_code'] != 'SUCCESS') {
                        throw new \Exception($res['return_msg']);
                    }                    
                    
                    break;
                case Payment::PAY_TYPE_ALIPAY_SCAN:
                    // $plugin = \Yii::$app->plugin->getPlugin(\Yii::$app->appPlatform);
                    // $aop = $plugin->getAlipayScanPay();

                    // $payType = $plugin->getAliPayConfig();
                    // $request = AlipayRequestFactory::create('alipay.trade.cancel', [
                    //     'biz_content' => [
                    //         'out_trade_no' => $paymentOrderUnion->order_no
                    //     ],
                    //     'app_cert_sn' => CertSN::getSn($payType->appcert),
                    //     'alipay_root_cert_sn' => CertSN::getSn($payType->alipay_rootcert, true)
                    // ]);

                    // $res = $aop->execute($request)->getData();

                    // \Yii::warning($res);
                    // if ($res['code'] != 10000) {
                    //     throw new \Exception($res['msg']);
                    // } 

                    break;
                default:
                    throw new \Exception('付款码支付方式异常');
                    break;
            }

            return true;
        } catch(\Exception $exception) {
            \Yii::error('撤销订单过程异常');
            \Yii::error($exception);
            throw $exception;
        }
    }
}
