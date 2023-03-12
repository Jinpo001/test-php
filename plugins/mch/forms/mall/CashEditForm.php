<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;


use app\core\payment\PaymentTransfer;
use app\core\response\ApiCode;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\order_pay_template\WithdrawErrorInfo;
use app\forms\common\template\order_pay_template\WithdrawSuccessInfo;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAccountLog;
use app\plugins\mch\models\MchCash;

class CashEditForm extends Model
{
    public $id;
    public $status;
    public $transfer_type;
    public $content;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'status', 'transfer_type'], 'integer'],
            [['content'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $mchCash = MchCash::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->id,
            ]);
            if (!$mchCash) {
                throw new \Exception('转账记录不存在');
            }

            $extra = \Yii::$app->serializer->decode($mchCash->type_data);
            if ($this->status != 3) {
                if ($mchCash->status != 0) {
                    throw new \Exception('转账记录不存在');
                }
                $mchCash->status = 1;
                $extra['apply_at'] = date('Y-m-d H:i:s', time());
                $extra['apply_content'] = $this->content;
            } else {
                $mchCash->status = 2;
                $mch = Mch::findOne($mchCash->mch_id);
                if (!$mch) {
                    throw new \Exception('商户不存在');
                }
                $extra['reject_at'] = date('Y-m-d H:i:s', time());
                $extra['reject_content'] = $this->content;
                // 拒绝后退回金额
                $mch->account_money = $mch->account_money + $mchCash->money;
                $res = $mch->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mch));
                }
            }
            $mchCash->type_data = \Yii::$app->serializer->encode($extra);
            $res = $mchCash->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($res));
            }

            $transaction->commit();

            if ($mchCash->status == 2) {
                $this->sendErrorTemplate($mchCash, '商户提现审核未通过');
                $this->sendSmsToUser($mchCash, '拒绝');
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function transfer()
    {

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var MchCash $mchCash */
            $mchCash = MchCash::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'id' => $this->id,
                'status' => 1,
            ]);
            if (!$mchCash) {
                throw new \Exception('转账记录不存在');
            }

            if ($mchCash->transfer_status != 0 ) {
                throw new \Exception('该转账记录已处理，请勿重复操作');
            }
            $extra = \Yii::$app->serializer->decode($mchCash->type_data);
            if ($this->transfer_type == 1) {
                //转账 确认打款
                if ($mchCash->type == 'auto') {
                    $data = [
                        'orderNo' => $mchCash->order_no,
                        'amount' => floatval($mchCash->money),
                        'user' => $mchCash->mch->user,
                        'title' => '商户提现,自动打款',
                    ];

                    if (!$mchCash->mch->user) {
                        throw new \Exception('商户未绑定小程序用户,无法自动打款');
                    }
                    $data['transferType'] = PlatformConfig::getInstance()->getPlatform($mchCash->mch->user, true);
                    $model = new PaymentTransfer($data);
                    \Yii::$app->payment->transfer($model);
                } elseif ($mchCash->type == 'balance') {
                    \Yii::$app->currency->setUser($mchCash->mch->user)->balance->add(
                        round($mchCash->money, 2),
                        '商户提现到余额',
                        \Yii::$app->serializer->encode($mchCash)
                    );
                } elseif ($mchCash->type == 'wx' || $mchCash->type == 'alipay' || $mchCash->type == 'bank') {
                } else {
                    throw new \Exception('提现异常');
                }

                $extra['remittance_at'] = date('Y-m-d H:i:s', time());
                $extra['remittance_content'] = $this->content;
                $mchCash->type_data = \Yii::$app->serializer->encode($extra);
                $mchCash->transfer_status = 1;
                $res = $mchCash->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mchCash));
                }
            } else {
                $mch = Mch::findOne($mchCash->mch_id);
                if (!$mch) {
                    throw new \Exception('商户不存在');
                }

                // 拒绝打款后退回金额
                $mch->account_money = $mch->account_money + $mchCash->money;
                $res = $mch->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mch));
                }
                $extra['remittance_at'] = date('Y-m-d H:i:s', time());
                $extra['remittance_content'] = $this->content;
                $mchCash->type_data = \Yii::$app->serializer->encode($extra);
                $mchCash->transfer_status = 2;
                $res = $mchCash->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($mchCash));
                }
            }

            $model = new MchAccountLog();
            if ($this->transfer_type == 1) {
                $model->desc = '提现申请已打款';
                $model->type = 1;
            } else {
                $model->desc = '提现申请拒绝打款';
                $model->type = 2;
            }

            $model->mall_id = \Yii::$app->mall->id;
            $model->mch_id = $mchCash->mch_id;
            $model->money = $mchCash->money;
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            $transaction->commit();
            if ($this->transfer_type == 1) {
                $this->sendSuccessTemplate($mchCash);
                $this->sendSmsToUser($mchCash, '通过');
            } else {
                $this->sendErrorTemplate($mchCash, '拒绝打款');
                $this->sendSmsToUser($mchCash, '拒绝');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }


    /**
     * @param MchCash $mchCash
     */
    private function sendSuccessTemplate($mchCash) {
        try {
            TemplateList::getInstance()->getTemplateClass(WithdrawSuccessInfo::TPL_NAME)->send([
                'price' => $mchCash->money,
                'serviceChange' => 0,
                'type' => $mchCash->getType($mchCash) . '账户',
                'remark' => '商户提现成功',
                'user' => $mchCash->mch->user,
                'page' => '/plugins/mch/mch/account/account'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }
    /**
     * @param MchCash $mchCash
     * @param $remark
     */
    private function sendErrorTemplate($mchCash, $remark) {
        try {
            TemplateList::getInstance()->getTemplateClass(WithdrawErrorInfo::TPL_NAME)->send([
                'price' => $mchCash->money,
                'remark' => $remark,
                'time' => $mchCash->created_at,
                'user' => $mchCash->mch->user,
                'page' => '/plugins/mch/mch/account/account'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param MchCash $cash
     * @param $remark
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($cash, $remark)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$cash->mch->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $cash->mch->user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => [$remark]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($cash->mch->user);
            $messageService->tplKey = WithdrawSuccessInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
