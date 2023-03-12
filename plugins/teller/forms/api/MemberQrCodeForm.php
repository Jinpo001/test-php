<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\models\Model;
use app\models\PaymentOrderUnion;
use app\models\QrCodeParameter;
use app\models\UserInfo;

class MemberQrCodeForm extends Model
{
    public $pay_code;

    public function rules()
    {
        return [
            [['pay_code'], 'integer']
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $userInfo = UserInfo::find()->andWhere([
                'user_id' => \Yii::$app->user->id,
            ])->one();

            if (!$userInfo) {
                throw new \Exception('会员数据异常');
            }
            $payCode = $this->getQrCodeToken();
            $common = new CommonQrCode();
            $common->qr_code_token = $payCode;
            $res = $common->getGeneralQrcode([
                'scene' => ['user_id' => $userInfo->user_id]
            ]);

            $data = [
                'pay_code' => $payCode,
                'file_path' => $res['file_path'],
                'expiry_date' => strtotime($res['created_at']) + 300
            ];
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $data,
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function getQrCodeToken()
    {
        $randLen = 6;
        $id = base_convert(substr(uniqid(), 0 - $randLen), 16, 10);
        if (strlen($id) > 10) {
            $id = substr($id, -10);
        } elseif (strlen($id) < 10) {
            $rLen = 10 - strlen($id);
            $id = $id . rand(pow(10, $rLen - 1), pow(10, $rLen) - 1);
        }
        $dateTimeStr = date('His');
        return $dateTimeStr . $id;
    }

    //GET
    public function searchPayCode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $parameter = QrCodeParameter::find()->andWhere([
                'token' => $this->pay_code,
                'mall_id' => \Yii::$app->mall->id,
            ])->one();

            if (!$parameter) {
                throw new \Exception('付款码不存在');
            }

            $newData['is_use'] = $parameter->use_number ? 1 : 0;

            if ($parameter->use_number >= 1) {
                $data = json_decode($parameter->data, true);
                $union = PaymentOrderUnion::find()->andWhere([
                    'mall_id' => \Yii::$app->mall->id,
                    'id' => $data['payment_order_id'],
                ])->with('paymentOrder')->one();

                $order = $union->paymentOrder[0]->order;
                
                $newData['amount'] = $union->amount;
                $newData['pay_type'] = '余额';
                $newData['order_no'] = $union->order_no;
                $newData['created_at'] = $union->created_at;
                $newData['member_discount_price'] = $order->member_discount_price;
                $newData['coupon_discount_price'] = $order->coupon_discount_price;
                $newData['integral_deduction_price'] = $order->integral_deduction_price;
                $newData['full_reduce_price'] = $order->full_reduce_price;
                $newData['total_goods_original_price'] = $order->total_goods_original_price;

                try {
                    $plugin = \Yii::$app->plugin->getPlugin('vip_card');
                    $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
                    if (in_array('vip_card', $permission) && $plugin) {
                        $list = $order->vipCardDiscount;
                        $vipCardDiscount = 0;
                        foreach ($list as $item) {
                            $vipCardDiscount += $item->discount;
                        }
                        $newData['vip_card_discount'] = price_format($vipCardDiscount);
                    }
                } catch (\Exception $e) {
                    //throw $e;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $newData,
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
