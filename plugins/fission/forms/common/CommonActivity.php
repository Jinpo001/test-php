<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/22
 * Time: 2:50 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\common;

use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCards;
use app\models\Mall;
use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivityReward;
use app\plugins\fission\models\FissionRewardLog;
use yii\helpers\Json;

class CommonActivity extends Model
{
    /**
     * @var Mall $mall
     */
    public $mall;

    public static function getInstance($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        $instance = new self();
        $instance->mall = $mall;
        return $instance;
    }

    public function getCoupon($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $list = Coupon::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $ids])
            ->select([
                'name', 'type', 'discount', 'discount_limit', 'min_price', 'sub_price', 'expire_type', 'expire_day',
                'appoint_type', 'begin_time', 'end_time', 'id'
            ])
            ->all();
        return array_column($list, null, 'id');
    }

    public function getGoods($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $goodsIds = array_column($ids, 'goods_id');
//        $goodsAttrIds = array_column($ids, null, 'goods_id');
        $list = Goods::find()->with(['goodsWarehouse', 'attr'])
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $goodsIds])
            ->all();
        $res = [];
        /** @var Goods[] $list */
        foreach ($list as $goods) {
            $attrGroups = $goods->resetAttr($goods->attr_groups);
            /** @var GoodsAttr[] $attrList */
            $attrList = array_column($goods->attr, null, 'id');
            foreach ($ids as $value) {
                if ($value['goods_id'] != $goods->id) {
                    continue;
                }
                $attrId = $value['attr_id'];
//            $attrId = $goodsAttrIds[$goods->id]['attr_id'];
                if (!isset($attrList[$attrId])) {
                    continue;
                }
                $attr = $attrGroups[$attrList[$attrId]->sign_id];
                $attrInfoArr = [];
                foreach ($attr as $item) {
                    $attrInfoArr[] = $item['attr_group_name'] . ':' . $item['attr_name'];
                }
                $res[$goods->id . '-' . $attrId] = [
                    'name' => $goods->goodsWarehouse->name,
                    'id' => $goods->id,
                    'price' => $goods->price,
                    'cover_pic' => $goods->goodsWarehouse->cover_pic,
                    'attr_id' => $attrId,
                    'attr' => $attr,
                    'attr_info' => implode(',', $attrInfoArr)
                ];
            }
        }
        return $res;
    }

    public function getCard($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $list = GoodsCards::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'id' => $ids, 'mch_id' => 0])
            ->select([
                'id', 'name', 'expire_type', 'expire_day', 'begin_time', 'end_time', 'pic_url', 'description'
            ])->all();
        return array_column($list, null, 'id');
    }

    /**
     * @param FissionRewardLog[] $rewardLogs
     * @return array
     */
    public function getRewards($rewardLogs)
    {
        $list = [];
        $couponIds = [];
        $goodsIds = [];
        $cardIds = [];
        foreach ($rewardLogs as $rewardLog) {
            $reward = new FissionActivityReward();
            $reward->attributes = Json::decode($rewardLog->reward, true);
            switch ($reward->status) {
                case 'coupon':
                    $couponIds[] = $reward->model_id;
                    break;
                case 'goods':
                    $goodsIds[] = [
                        'goods_id' => $reward->model_id,
                        'attr_id' => $reward->attr_id
                    ];
                    break;
                case 'card':
                    $cardIds[] = $reward->model_id;
                    break;
                default:
            }
        }
        $couponList = $this->getCoupon($couponIds);
        $cardList = $this->getCard($cardIds);
        $goodsList = $this->getGoods($goodsIds);
        foreach ($rewardLogs as $rewardLog) {
            $reward = new FissionActivityReward();
            $reward->attributes = Json::decode($rewardLog->reward, true);
            $item = [];
            switch ($reward->status) {
                case 'cash':
                    $item = [
                        'name' => $rewardLog->real_reward . '元现金红包',
                    ];
                    break;
                case 'balance':
                    $item = [
                        'name' => $rewardLog->real_reward . '元商城余额',
                    ];
                    break;
                case 'integral':
                    $item = [
                        'name' => $rewardLog->real_reward . '元积分',
                    ];
                    break;
                case 'coupon':
                    if (!isset($couponList[$reward->model_id])) {
                        continue;
                    }
                    /** @var Coupon $coupon */
                    $coupon = $couponList[$reward->model_id];
                    $item = [
                        'name' => $coupon->name,
                    ];
                    break;
                case 'card':
                    if (!isset($cardList[$reward->model_id])) {
                        continue;
                    }
                    /** @var GoodsCards $card */
                    $card = $cardList[$reward->model_id];
                    $item = [
                        'name' => $card->name,
                    ];
                    break;
                case 'goods':
                    if (!isset($goodsList[$reward->model_id . '-' . $reward->attr_id])) {
                        continue;
                    }
                    /** @var Goods $goods */
                    $goods = $goodsList[$reward->model_id . '-' . $reward->attr_id];
                    $item = [
                        'id' => $goods['id'],
                        'name' => $goods['name'],
                        'cover_pic' => $goods['cover_pic'],
                        'exchange_type' => $reward->exchange_type,
                        'attr_id' => $reward->attr_id
                    ];
                    break;
                default:
            }
            $isExchange = $rewardLog->is_exchange;
            if ($rewardLog->expire_time > 0) {
                $endTime = strtotime($rewardLog->created_at) + $rewardLog->expire_time * 86400;
                $isExchange = $isExchange ? 1 : ($endTime <= time() ? 2 : 0);
                $endAt = mysql_timestamp($endTime);
            } else {
                $endAt = '永久';
            }
            $activity = Json::decode($rewardLog->activityLog->activity, true);
            $item = array_merge($item, [
                'status' => $reward->status,
                'time' => $rewardLog->created_at,
                'type' => $isExchange,
                'end_at' => $endAt,
                'reward_log_id' => $rewardLog->id,
                'real_reward' => $rewardLog->real_reward,
                'activity_name' => $activity['name']
            ]);
            $list[$rewardLog->id] = $item;
        }
        return $list;
    }

    /**
     * @return array
     * 红包种类
     */
    public function statusList()
    {
        return [
            'cash' => '现金',
            'balance' => '商城余额',
            'integral' => '商城积分',
            'goods' => '赠品',
            'coupon' => '优惠券',
            'card' => '卡券',
        ];
    }
}
