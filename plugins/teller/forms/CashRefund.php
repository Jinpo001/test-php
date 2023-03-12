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


use app\forms\common\refund\BaseRefund;
use app\models\PaymentRefund;
use app\plugins\teller\Plugin;

class CashRefund extends BaseRefund
{
    /**
     * POS机退款
     * @param PaymentRefund $paymentRefund
     * @param \app\models\PaymentOrderUnion $paymentOrderUnion
     * @return bool|mixed
     * @throws PaymentException
     */
    public function refund($paymentRefund, $paymentOrderUnion)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->save($paymentRefund);
            $t->commit();
            return true;
        } catch (\Exception $e) {
            $t->rollBack();
        }
    }

    /**
     * @param PaymentRefund $paymentRefund
     * @throws \Exception
     */
    private function save($paymentRefund)
    {
        $paymentRefund->is_pay = 1;
        $paymentRefund->pay_type = 9;
        if (!$paymentRefund->save()) {
            throw new \Exception($this->getErrorMsg($paymentRefund));
        }
    }
}
