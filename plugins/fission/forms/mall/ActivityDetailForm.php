<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/16
 * Time: 5:28 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsCards;
use app\plugins\fission\forms\common\CommonActivity;
use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivity;

class ActivityDetailForm extends Model
{
    public $id;

    public function rules()
    {
        return [
            ['id', 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            /* @var FissionActivity $activity */
            $activity = FissionActivity::find()->with('rewards')
                ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id])
                ->one();
            if (!$activity) {
                throw new \Exception('活动不存在');
            }
            if (strtotime($activity->end_time) < time() && $activity->status == 1) {
                throw new \Exception('活动已结束无法编辑');
            }
            $res = [
                'id' => $activity->id,
                'name' => $activity->name,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'style' => $activity->style,
                'number' => $activity->number,
                'app_share_title' => $activity->app_share_title,
                'app_share_pic' => $activity->app_share_pic,
                'rule_title' => $activity->rule_title,
                'rule_content' => $activity->rule_content,
                'expire_time' => $activity->expire_time,
            ];
            $couponIds = [];
            $goodsIds = [];
            $cardIds = [];
            foreach ($activity->rewards as $reward) {
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
            $commonActivity = CommonActivity::getInstance();
            $couponList = $commonActivity->getCoupon($couponIds);
            $goodsList = $commonActivity->getGoods($goodsIds);
            $cardList = $commonActivity->getCard($cardIds);
            foreach ($activity->rewards as $key => $reward) {
                $goods = null;
                $card = null;
                $coupon = null;
                $attr = null;
                $minNumber = $reward->min_number;
                $maxNumber = $reward->max_number;
                switch ($reward->status) {
                    case 'coupon':
                        $coupon = $couponList[$reward->model_id] ?? null;
                        break;
                    case 'goods':
                        $goods = $goodsList[$reward->model_id . '-' . $reward->attr_id] ?? null;
                        $attr = null;
                        break;
                    case 'card':
                        $card = $cardList[$reward->model_id] ?? null;
                        break;
                    case 'integral':
                        $minNumber = intval($minNumber);
                        $maxNumber = intval($maxNumber);
                        break;
                    default:
                }
                if ($reward->type == 0) {
                    $res = array_merge($res, [
                        'reward_status' => $reward->status,
                        'reward_coupon_id' => $reward->model_id,
                        'reward_min_number' => $minNumber,
                        'reward_max_number' => $maxNumber,
                        'reward_send_type' => $reward->send_type,
                        'coupon' => $coupon
                    ]);
                } else {
                    if ($reward->level == 'main') {
                        $res['level_list'][$reward->type] = [
                            'status' => $reward->status,
                            'people_number' => $reward->people_number,
                            'model_id' => $reward->model_id,
                            'exchange_type' => $reward->exchange_type,
                            'min_number' => $minNumber,
                            'max_number' => $maxNumber,
                            'send_type' => $reward->send_type,
                            'goods' => $goods,
                            'coupon' => $coupon,
                            'card' => $card,
                            'second' => [
                                'status' => 'cash',
                                'min_number' => 0,
                                'max_number' => 0,
                                'send_type' => 'average',
                            ],
                            'attr_id' => $reward->attr_id,
                        ];
                    } else {
                        $res['level_list'][$reward->type]['second'] = [
                            'status' => $reward->status,
                            'min_number' => $minNumber,
                            'max_number' => $maxNumber,
                            'send_type' => $reward->send_type,
                        ];
                    }
                }
            }
            $res['level_list'] = array_values($res['level_list']);
            return $this->success([
                'msg' => '获取成功',
                'detail' => $res
            ]);
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage(),
                'error' => $exception
            ]);
        }
    }
}
