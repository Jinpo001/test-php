<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web\order;


use app\forms\api\recharge\RechargePayNotify;
use app\models\MallMembers;
use app\models\Model;
use app\models\RechargeOrders;
use app\models\User;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\forms\common\PushOrder;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerPushOrder;
use app\plugins\teller\models\TellerSales;
use app\plugins\teller\models\TellerWorkLog;

class TellerRechargePayNotify extends RechargePayNotify
{
    public function notify($paymentOrder)
    {
        try {
            /* @var RechargeOrders $reOrder */
            $reOrder = RechargeOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$reOrder) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            if ($reOrder->is_pay == 1) {
                throw new \Exception('订单已处理');
            }

            $reOrder->is_pay = 1;
            $reOrder->pay_time = date('Y-m-d H:i:s', time());
            $res = $reOrder->save();

            if (!$res) {
                throw new \Exception('充值订单支付状态更新失败');
            }

            $user = User::findOne($reOrder->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }
            $this->sendData($user, $reOrder);
            $this->setWorkLog($reOrder);
            $this->pushOrder($reOrder);
            $this->setPushOrder($reOrder);
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    // 生成提成订单
    private function setWorkLog($reOrder)
    {
        \Yii::warning('收银台余额充值统计开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tellerOrder = TellerOrders::find()->andWhere([
                're_order_id' => $reOrder->id
            ])->with(['sales', 'cashier', 'reOrder'])->one();

            if (!$tellerOrder) {
                throw new \Exception('收银台订单不存在');
            }

            $tellerOrder->is_statistics = 1;
            $res = $tellerOrder->save();
            if (!$res) {
                throw new \Exception((new Model())->getErrorMsg($tellerOrder));
            }

            $workLog = $tellerOrder->workLog;
            if (!$workLog) {
                throw new \Exception('无交班记录');
            }

            $extra = json_decode($workLog->extra_attributes, true);
            $payPrice = $reOrder->pay_price;
            $extra['recharge']['total_recharge'] = price_format($extra['recharge']['total_recharge'] + $payPrice);
            $extra['recharge']['total_order'] += 1;
            switch ($reOrder->paymentOrder->pay_type) {
                case 9:
                    $extra['recharge']['cash_recharge'] = price_format($extra['recharge']['cash_recharge'] + $payPrice);
                    break;
                case 10:
                    $extra['recharge']['pos_recharge'] = price_format($extra['recharge']['pos_recharge'] + $payPrice);
                    break;
                case 11:
                    $extra['recharge']['wechat_recharge'] = price_format($extra['recharge']['wechat_recharge'] + $payPrice);
                    break;
                case 12:
                    $extra['recharge']['alipay_recharge'] = price_format($extra['recharge']['alipay_recharge'] + $payPrice);
                    break;
                default:
                    throw new \Exception('统计未知支付类型' . $reOrder->paymentOrder->pay_type);
                    break;
            }
            $workLog->extra_attributes = json_encode($extra);
            $workLog->save();
            $transaction->commit();
            \Yii::warning('收银台余额充值统计结束');
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('收银台余额充值统计异常');
            \Yii::error($exception);
        }
    }

    // 生成提成订单
    private function pushOrder($reOrder)
    {
        \Yii::warning('收银台余额充值提成订单生成开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $tellerOrder = TellerOrders::find()->andWhere([
                're_order_id' => $reOrder->id
            ])->with(['sales', 'cashier'])->one();

            if (!$tellerOrder) {
                throw new \Exception('收银台订单不存在');
            }

            $tellerOrder->is_pay = 1;
            $tellerOrder->pay_type = $reOrder->paymentOrder->pay_type;
            $tellerOrder->save();

            $common = new CommonTellerSetting();
            $common->mall_id = $tellerOrder->mall_id;
            $setting = $common->search();

            // 收银员提成
            if ($setting['is_cashier_push'] && $tellerOrder->cashier) {
                $pushOrder = new TellerPushOrder();
                $pushOrder->mall_id = $tellerOrder->mall_id;
                $pushOrder->mch_id = $tellerOrder->mch_id;
                $pushOrder->user_type = TellerCashier::USER_TYPE;
                $pushOrder->order_type = TellerPushOrder::ORDER_TYPE_RECHARGE;
                $pushOrder->re_order_id = $tellerOrder->re_order_id;
                $pushOrder->push_type = $setting['cashier_push_type'] == 1 ? TellerPushOrder::PUSH_TYPE_ORDER : TellerPushOrder::PUSH_TYPE_PERCENT;
                $pushOrder->push_order_money = $setting['cashier_push'];
                $pushOrder->push_percent = $setting['cashier_push_percent'];
                $pushOrder->cashier_id = $tellerOrder->cashier->id;
                $pushOrder->teller_order_id = $tellerOrder->id;
                $pushOrder->status = TellerPushOrder::ORDER_STATUS_PENDING;
                $res = $pushOrder->save();
                
                if (!$res) {
                    throw new OrderException((new Model())->getErrorMsg($pushOrder));
                }
            }

            if ($setting['is_sales_push'] && $tellerOrder->sales) {
                $pushOrder = new TellerPushOrder();
                $pushOrder->mall_id = $tellerOrder->mall_id;
                $pushOrder->mch_id = $tellerOrder->mch_id;
                $pushOrder->user_type = TellerSales::USER_TYPE;
                $pushOrder->order_type = TellerPushOrder::ORDER_TYPE_RECHARGE;
                $pushOrder->re_order_id = $tellerOrder->re_order_id;
                $pushOrder->push_type = $setting['sales_push_type'] == 1 ? TellerPushOrder::PUSH_TYPE_ORDER : TellerPushOrder::PUSH_TYPE_PERCENT;
                $pushOrder->push_percent = $setting['sales_push_percent'];
                $pushOrder->sales_id = $tellerOrder->sales->id;
                $pushOrder->teller_order_id = $tellerOrder->id;
                $pushOrder->status = TellerPushOrder::ORDER_STATUS_PENDING;
                $res = $pushOrder->save();

                if (!$res) {
                    throw new OrderException((new Model())->getErrorMsg($pushOrder));
                }
            }
            $transaction->commit();
            \Yii::warning('收银台余额充值提成订单生成结束');
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::error('收银台提成订单生成异常');
            \Yii::error($exception);
        }
    }

    // 提成订单结算
    public function setPushOrder($reOrder)
    {
        \Yii::warning('收银台提成结算开始');
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $pushOrders = TellerPushOrder::find()->andWhere([
                're_order_id' => $reOrder->id,
                'order_type' => TellerPushOrder::ORDER_TYPE_RECHARGE,
                'mall_id' => $reOrder->mall_id,
                'is_delete' => 0
            ])->with('reOrder')->all();

            foreach ($pushOrders as $pushOrder) {
                $pushOrder->status = TellerPushOrder::ORDER_STATUS_FINISH;
                switch ($pushOrder->push_type) {
                    // 按订单
                    case TellerPushOrder::PUSH_TYPE_ORDER:
                        $pushOrder->push_money = $pushOrder->push_order_money;
                        break;
                    // 按订单金额百分比
                    case TellerPushOrder::PUSH_TYPE_PERCENT:
                        $pushMoney = $pushOrder->reOrder->pay_price * ($pushOrder->push_percent / 100);
                        $pushOrder->push_money = price_format($pushMoney);
                        break;
                    default:
                        \Yii::error(sprintf('提成订单%s未知提成类型%s', $pushOrder->id, $pushOrder->push_type));
                        break;
                }
                $pushOrder->save();

                if ($pushOrder->user_type == TellerCashier::USER_TYPE) {
                    $cashier = TellerCashier::findOne($pushOrder->cashier_id);

                    if (!$cashier) {
                        throw new \Exception(sprintf('收银员不存在%s', $pushOrder->cashier_id));
                    }

                    $cashier->push_money = $cashier->push_money + $pushOrder->push_money;
                    $cashier->sale_money = $cashier->sale_money + ($pushOrder->reOrder->pay_price - $pushOrder->tellerOrder->refund_money);
                    $res = $cashier->save();

                    if (!$res) {
                        throw new \Exception((new Model())->getErrorMsg($cashier));
                    }

                } else {
                    $sales = TellerSales::findOne($pushOrder->sales_id);

                    if (!$sales) {
                        throw new \Exception(sprintf('导购员不存在%s', $pushOrder->sales_id));
                    }

                    $sales->push_money = $sales->push_money + $pushOrder->push_money;
                    $sales->sale_money = $sales->sale_money + ($pushOrder->reOrder->pay_price - $pushOrder->tellerOrder->refund_money);
                    $res = $sales->save();

                    if (!$res) {
                        throw new \Exception((new Model())->getErrorMsg($sales));
                    }
                }
            }
            $transaction->commit();
            \Yii::warning('收银台提成结算结束');
        }catch(\Exception $exception) {
            $transaction->rollBack();
            \Yii::warning(sprintf('收银台余额充值提成结算出错,订单ID%s', $reOrder->id));
            \Yii::warning($exception);
        }
    }
}
