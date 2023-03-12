<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Coupon;
use app\models\GoodsCards;
use app\models\Recharge;

class RechargeForm extends Model
{
    public function rules()
    {
        return [];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $list = Recharge::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])
                ->with(['member' => function ($query) {
                    $query->where(['status' => 1, 'is_delete' => 0]);
                }])
                ->all();

            $list = array_map(function($item) {
                $member = [];
                if ($item->member) {
                    $member = [
                        'id' => $item->member->id,
                        'level' => $item->member->level,
                        'name' => $item->member->name,
                    ];
                }
                $coupons = [];
                $coupons_num = 0;
                if ($item['send_type'] & Recharge::R_COUPON) {
                    $send_coupon = \yii\helpers\BaseJson::decode($item['send_coupon'] ?: '{}');
                    if(is_array($send_coupon)) {
                        foreach ($send_coupon as $i) {
                            $coupon = Coupon::find()->andWhere(['id' => $i['coupon_id']])->one();
                            $coupons_num += $i['send_num'];
                            $newCoupon = \yii\helpers\ArrayHelper::toArray($coupon);
                            array_push($coupons, array_merge(['num' => $i['send_num']], $newCoupon));
                        }
                    }
                }
                $cards = [];
                $cards_num = 0;
                if ($item['send_type'] & Recharge::R_CARD) {
                    $send_card = \yii\helpers\BaseJson::decode($item['send_card'] ?: '{}');
                    if(is_array($send_card)) {     
                        foreach ($send_card as $i) {
                            $card = GoodsCards::find()->andWhere(['id' => $i['id']])->asArray()->one();
                            $cards_num += $i['num'];
                            array_push($cards, array_merge(['num' => $i['num']], $card));
                        }
                    }
                }
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'pay_price' => $item->pay_price,
                    'send_price' => $item->send_price,
                    'send_integral' => $item->send_integral,
                    'send_card' => $cards,
                    'send_card_num' => $cards_num,
                    'send_coupon' => $coupons,
                    'send_coupon_num' => $coupons_num,
                    'lottery_limit' => $item->lottery_limit,
                    'send_type' => $item->send_type,
                    'member' => $member
                ];
            }, $list);
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list
                ],
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
