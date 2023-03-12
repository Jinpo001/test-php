<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/11/7
 * Time: 9:21
 */

namespace app\forms\common\refund;

use Alipay\AlipayRequestFactory;
use app\core\payment\PaymentException;
use app\forms\common\CertSN;
use app\helpers\ArrayHelper;
use app\models\PaymentRefund;

class AliH5Refund extends BaseRefund
{
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $plugin = \Yii::$app->plugin->getPlugin($paymentOrderUnion->platform);
            $aliappConfig = $plugin->getAliConfig($paymentOrderUnion->platform);
            $aliappConfig = ArrayHelper::toArray($aliappConfig);
            $aop = $plugin->getAliAopClient($paymentOrderUnion->platform);
            $request = AlipayRequestFactory::create('alipay.trade.refund', [
                'biz_content' => [
                    'out_trade_no' => $paymentOrderUnion->order_no,
                    'refund_amount' => $paymentRefund->amount,
                    'out_request_no' => $paymentRefund->order_no,
                ],
                'app_cert_sn' => CertSN::getSn($aliappConfig['appcert']),
                'alipay_root_cert_sn' => CertSN::getSn($aliappConfig['alipay_rootcert'], true),
            ]);
            $res = $aop->execute($request)->getData();
            if ($res['code'] != 10000) {
                throw new \Exception($res['sub_msg']);
            }
            $this->save($paymentRefund);
            $t->commit();
            return true;
        } catch (\Exception $e) {
            dd($e);
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }

    /**
     * @param PaymentRefund $paymentRefund
     * @throws \Exception
     */
    private function save($paymentRefund)
    {
        $paymentRefund->is_pay = 1;
        $paymentRefund->pay_type = 8;
        if (!$paymentRefund->save()) {
            throw new \Exception($this->getErrorMsg($paymentRefund));
        }
    }
}
