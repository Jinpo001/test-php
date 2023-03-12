<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\api\recharge;

use app\core\response\ApiCode;
use app\models\Coupon;
use app\models\GoodsCards;
use app\models\Model;
use app\models\Recharge;

class RechargeForm extends Model
{
    public $pay_price;
    public $send_price;

    public function rules()
    {
        return [
            [['pay_price', 'send_price'], 'double']
        ];
    }

    public function getIndex()
    {
        $list = Recharge::find()
            ->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])
            ->with(['member' => function ($query) {
                $query->where(['status' => 1, 'is_delete' => 0]);
            }])
            ->with(['member.rights'])
            ->asArray()
            ->all();


        $list = array_map(function ($item) {
            if ($item['send_type'] & Recharge::R_COUPON) {
                $send_coupon = \yii\helpers\BaseJson::decode($item['send_coupon'] ?: '{}');
                $item['coupons'] = [];
                foreach ($send_coupon as $i) {
                    $coupon = Coupon::find()->andWhere(['id' => $i['coupon_id']])->one();
                    $newCoupon = \yii\helpers\ArrayHelper::toArray($coupon);
                    $newCoupon['content'] = '';
                    if ($coupon->appoint_type == 1) {
                        $catList = [];
                        foreach ($coupon->cat as $cat) {
                            $catList[] = $cat->name;
                        }
                        $newCoupon['content'] .= implode('、', $catList) . '使用';
                    } elseif ($coupon->appoint_type == 2) {
                        $goodsWarehouseList = [];
                        foreach ($coupon->goodsWarehouse as $goodsWarehouse) {
                            $goodsWarehouseList[] = $goodsWarehouse->name;
                            $newCoupon['content'] .= $goodsWarehouse->name . '、';
                        }
                        $newCoupon['content'] .= implode('、', $goodsWarehouseList) . '使用';
                    } elseif ($coupon->appoint_type == 4) {
                        $newCoupon['content'] = '仅限当面付使用';

                    } elseif ($coupon->appoint_type == 4) {
                        $newCoupon['content'] = '仅限兑换中心使用';

                    } else {
                        $newCoupon['content'] = '全场通用';
                    }
                    array_push($item['coupons'], array_merge(['num' => $i['send_num']], $newCoupon));
                }
            }

            if ($item['send_type'] & Recharge::R_CARD) {
                $send_card = \yii\helpers\BaseJson::decode($item['send_card'] ?: '{}');
                $item['cards'] = [];
                foreach ($send_card as $i) {
                    $cards = GoodsCards::find()->andWhere(['id' => $i['id']])->asArray()->one();
                    array_push($item['cards'], array_merge(['num' => $i['num']], $cards));
                }
            }
            return $item;
        }, $list);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list
            ]
        ];
    }
}
