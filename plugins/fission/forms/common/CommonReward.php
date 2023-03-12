<?php


namespace app\plugins\fission\forms\common;


use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCards;
use app\plugins\diy\forms\api\UserCoupon;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionRewardLog;

class CommonReward
{
    public static function addInfo($reward, $result_id = 0)
    {
        if (is_string($reward)) {
            $reward = \yii\helpers\BaseJson::decode($reward);
        }
        switch ($reward['status']) {
            case 'cash':
                $reward['send_type'] === 'average' && $reward['max_number'] = $reward['min_number'];
                break;
            case 'coupon':
                $coupon = Coupon::findOne($reward['model_id']);
                $reward['coupon'] = [
                    'id' => $coupon['id'],
                    'user_coupon_id' => $result_id,
                    'name' => $coupon['name'],
                    'type' => $coupon['type'],
                    'discount' => $coupon['discount'],
                    'min_price' => $coupon['min_price'],
                    'sub_price' => $coupon['sub_price'],
                    'appoint_type' => $coupon['appoint_type'],
                    'total_count' => $coupon['total_count'],
                ];
                break;
            case 'goods':
                $goods = Goods::findOne($reward['model_id']);
                $attr = GoodsAttr::findOne($reward['attr_id']);
                $attr_list = (new Goods())->signToAttr($attr->sign_id, $goods->attr_groups);
                $reward['goods'] = [
                    'id' => $goods->id,
                    'cover_pic' => $goods->coverPic,
                    'name' => $goods->name,
                    'attr_id' => $attr->id,
                    'attr_list' => $attr_list,
                    'stock' => $attr->stock,
                ];
                break;
            case 'card':
                $card = GoodsCards::findOne($reward['model_id']);
                $reward['card'] = [
                    'id' => $card['id'],
                    'user_card_id' => $result_id,
                    'name' => $card['name'],
                    'pic_url' => $card['pic_url'],
                    'total_count' => $card['total_count']
                ];
                break;
        }
        return $reward;
    }

    private function formatArray($activityLogModel, $rewards)
    {
        $count = FissionActivityLog::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'invite_user_id' => $activityLogModel->user_id,
            'activity_id' => $activityLogModel->activity_id,
            'invite_activity_log_id' => $activityLogModel->id,
            'is_delete' => 0
        ])->count();

        $p = 0;
        $reward = array_map(function ($reward) use (&$p, &$count, &$activity_log_id) {
            extract($reward);
            $p += $people_number;
            if ($is_exchange == -2 && $p <= $count && $activity_log_id != -1) $is_exchange = -1;
            $c = [
                'is_exchange' => $is_exchange,
                'result_id' => $result_id,
                'reward_id' => $id,
                'real_reward' => $real_reward,
                'max_number' => $max_number,
                'min_number' => $min_number,
                'level' => $level,
                'exchange_type' => $exchange_type,
                'status' => $status,
                'type' => $type,
                'people_number' => $p,
                'model_id' => $model_id,
                'attr_id' => $attr_id,
                'reward_log_id' => $reward_log_id,
                'send_type' => $send_type,
            ];
            return self::addInfo($c);
        }, $rewards);
        unset($p);
        unset($count);
        return $reward;
    }

    //todo 权限待测试
    public function couponValidate($reward)
    {
        $coupon = Coupon::findOne([
            'id' => $reward['model_id'],
            'is_delete' => 0,
        ]);
        if (!$coupon) {
            return false;
        }
        //有效期
        if ($coupon->expire_type == 2 && $coupon->end_time < date('Y-m-d H:i:s')) {
            return false;
        }

        if ($coupon->total_count != -1) {
            if ($coupon->total_count <= 0) {
                return false;
            }
        }
        return true;
    }

    private function cardValidate($reward)
    {
        /** @var GoodsCards $goodsCards */
        $goodsCards = GoodsCards::find()->where([
            'id' => $reward['model_id'],
            'is_delete' => 0,
        ])->one();
        if (!$goodsCards) {
            return false;
        }
        //有效期
        if ($goodsCards->expire_type == 2 && $goodsCards->end_time < date('Y-m-d H:i:s')) {
            return false;
        }

        if ($goodsCards->total_count != -1) {
            if ($goodsCards->total_count <= 0) {
                return false;
            }
        }
        return true;
    }

    private function goodsValidate($reward)
    {
        $attr = GoodsAttr::find()->where([
            'id' => $reward['attr_id'],
            'goods_id' => $reward['model_id'],
            'is_delete' => 0,
        ])->one();
        if (!$attr || !$attr->goods) {
            return false;
        }
        //上架
        if ($attr->goods->is_delete == 1 || !$attr->goods->status) {
            return false;
        }
        //销售时间
        if ($attr->goods->is_time && (date('Y-m-d H:i:s') < $attr->goods->sell_begin_time || date('Y-m-d H:i:s') > $attr->goods->sell_end_time)) {
            return false;
        }
        //库存
        if ($attr->stock == 0) {
            return false;
        }
        return true;
    }

    ///////ENDENDENDEND////////
    public function homeFormat($activityLogModel)
    {
        $rewards = \yii\helpers\BaseJson::decode($activityLogModel->rewards);

        $all = FissionRewardLog::find()->select('id,reward_id, reward_type, is_exchange, real_reward, result_id')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'user_id' => $activityLogModel->user_id,
            'activity_log_id' => $activityLogModel->id
        ])->asArray()->all();
        $arr = new \SplFixedArray(6);
        foreach ($rewards as $reward) {
            $reward['is_exchange'] = -2;
            $reward['real_reward'] = 0;
            $reward['result_id'] = 0;
            $reward['reward_log_id'] = 0;
            foreach ($all as $item) {
                if ($reward['type'] == $item['reward_type'] && $reward['id'] == $item['reward_id']) {
                    $reward['real_reward'] = $item['real_reward'];
                    $reward['is_exchange'] = $item['is_exchange'];
                    $reward['result_id'] = $item['result_id'];
                    $reward['reward_log_id'] = $item['id'];
                }
            }
            if (is_null($value = $arr->offsetGet($reward['type']))) {
                $arr->offsetSet($reward['type'], $reward);
            } else {
                $return = $this->compare($value, $reward);
                $return && $arr->offsetSet($reward['type'], $return);
            }
        }
        $arr = $arr->toArray();
        return $this->formatArray($activityLogModel, array_filter($arr, function ($item) {
            return !is_null($item);
        }));
    }

    private function compare($data1, $data2)
    {
        if (intval($data1['level'] === 'main') ^ intval($data2['level'] === 'main')) {
            if ($data1['is_exchange'] != -2) {
                return $data1;
            }
            if ($data2['is_exchange'] != -2) {
                return $data2;
            }
            if ($data1['level'] === 'main') {
                $main = $data1;
                $secondary = $data2;
            } else {
                $main = $data2;
                $secondary = $data1;
            }
            $is_main = true;
            if ($main['status'] === 'coupon') {
                $is_main = $this->couponValidate($main);
            }
            if ($main['status'] === 'card') {
                $is_main = $this->cardValidate($main);
            }
            if ($main['status'] === 'goods') {
                $is_main = $this->goodsValidate($main);
            }

            return $is_main ? $main : $secondary;
        } else {
            die('数据异常');
        }
    }

}