<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/16 10:46
 */


namespace app\plugins\teller\forms\web\order;

use app\core\payment\Payment;
use app\core\response\ApiCode;
use app\forms\api\order\OrderException;
use app\models\PaymentOrderUnion;
use app\models\QrCodeParameter;
use app\models\User;
use app\plugins\teller\forms\common\CommonTellerSetting;

class TellerPayment extends Payment
{
    public $user_id;
    public $auth_code;//付款码

    public $balance_type;
    public $pay_password; // 支付密码
    public $pay_code; //会员付款码
    public $is_need_pay_password = 1;

    private $tellerSetting;

    public function getUser()
    {
        if (!$this->user_id) {
            throw new OrderException('请传入user_id');
        }

        $user = User::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->user_id])->one();

        if (!$user) {
            throw new OrderException('用户不存在');
        }

        return $user;
    }

    public function onLinePay($id, $pay_type)
    {
        try {
            $paymentOrderUnion = PaymentOrderUnion::findOne(['id' => $id]);
            if (!$paymentOrderUnion) {
                throw new PaymentException('待支付订单不存在。');
            }
            if ($paymentOrderUnion->amount <= 0) {
                return $this->otherPay($id, 'cash');
            } else {
                $data = $this->getPayData($id, $pay_type);
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '支付成功',
                    'data' => $data
                ];
            }
            throw new PaymentException('支付类型异常');
        }catch(\Exception $exception) {
            $error = explode(',', $exception->getMessage());
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => count($error) == 2 ? $error[1] : $error[0]
            ];
        }
    }

    // 其它支付
    public function otherPay($id, $payType)
    {
        try {
            switch ($payType) {
                case \app\core\payment\Payment::PAY_TYPE_BALANCE:
                    $this->tellerSetting = (new CommonTellerSetting())->search();
                    if (!in_array($this->balance_type, $this->tellerSetting['balance_type_list'])) {
                        throw new \Exception('余额支付类型不支持' . json_encode($this->tellerSetting['balance_type_list']));
                    }

                    switch ($this->balance_type) {
                        case Payment::PAY_BALANCE_TYPE_PASSWORD:
                            $this->payBuyBalance($id, $this->pay_password, ['is_need_pay_password' => $this->is_need_pay_password]);
                            break;
                        // 付款码支付
                        case Payment::PAY_BALANCE_TYPE_QR_CODE:
                            if (!$this->pay_code) {
                                throw new \Exception('请扫描会员码');
                            }

                            $parameter = QrCodeParameter::find()->andWhere([
                                'mall_id' => \Yii::$app->mall->id,
                                'token' => $this->pay_code,
                                'use_number' => 0
                            ])->one();

                            if (!$parameter) {
                                throw new \Exception('付款码已失效');
                            }

                            if ($this->user_id && $this->user_id != $parameter->user_id) {
                                throw new \Exception('当前会员不可用该付款码');
                            }

                            // 付款码只有5分钟有效时间
                            if (time() - strtotime($parameter->created_at) > 300) {
                                throw new \Exception('付款码已失效');
                            }
                            
                            $this->payBuyBalance($id, $this->pay_code, ['is_need_pay_password' => $this->is_need_pay_password]);
                            break;
                        default:
                            throw new \Exception('余额支付类型异常' . $this->balance_type);
                            break;
                    }
                    break;
                case \app\core\payment\Payment::PAY_TYPE_POS:
                    $this->payBuyPos($id);
                    break;
                case \app\core\payment\Payment::PAY_TYPE_CASH:
                    $this->payBuyCash($id);
                    break;
                default:
                    throw new OrderException('支付类型不支持');
                    break;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '支付成功。',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function isVerifyPayPassword()
    {
        $isTrue = $this->tellerSetting['is_balance_pay_password'] ? true : false;

        if ($this->balance_type == Payment::PAY_BALANCE_TYPE_QR_CODE) {
            $isTrue = false;
        }
        return $isTrue;
    }
}
