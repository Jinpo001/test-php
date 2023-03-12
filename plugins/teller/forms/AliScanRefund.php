<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/2/18
 * Time: 16:05
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\teller\forms;


use Alipay\AlipayRequestFactory;
use Alipay\Exception\AlipayException;
use app\core\payment\PaymentException;
use app\forms\common\CertSN;
use app\forms\common\refund\BaseRefund;
use app\models\PaymentRefund;
use app\plugins\teller\Plugin;

class AliScanRefund extends BaseRefund
{
    /**
     * 支付宝退款
     * @param PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $plugin = new Plugin();
            $aop = $plugin->getAlipayScanPay();
            $payType = $plugin->getAliPayConfig();
            $request = AlipayRequestFactory::create('alipay.trade.refund', [
                'biz_content' => [
                    'out_trade_no' => $paymentRefund->out_trade_no,
                    'refund_amount' => $paymentRefund->amount,
                    'out_request_no' => $paymentRefund->order_no,// 支付宝部分退款此参数居必传
                ],
                'app_cert_sn' => CertSN::getSn($payType->appcert),
                'alipay_root_cert_sn' => CertSN::getSn($payType->alipay_rootcert, true)
            ]);

            $result = $aop->execute($request)->getData();

            $this->save($paymentRefund);
            $t->commit();
            return true;
        } catch (AlipayException $e) {
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        } catch (\Exception $e) {
            $t->rollBack();
            throw new PaymentException('请检查支付证书是否填写正确');
        }
    }

    /**
     * @param PaymentRefund $paymentRefund
     * @throws \Exception
     */
    private function save($paymentRefund)
    {
        $paymentRefund->is_pay = 1;
        $paymentRefund->pay_type = 4;
        if (!$paymentRefund->save()) {
            throw new \Exception($this->getErrorMsg($paymentRefund));
        }
    }
}
