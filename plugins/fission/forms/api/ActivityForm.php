<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/21
 * Time: 2:47 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\api;

use app\models\User;
use app\plugins\fission\forms\common\CommonActivity;
use app\plugins\fission\forms\Model;
use app\plugins\fission\models\FissionActivity;
use app\plugins\fission\models\FissionActivityLog;
use app\plugins\fission\models\FissionActivityReward;
use yii\helpers\Json;

class ActivityForm extends Model
{
    public $id;
    public $user_id;

    /**
     * @var FissionActivityReward[] $rewards
     */
    public $rewards;

    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer']
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $activity = $this->getActivity();
            $rewardsList = $this->getRewardsList($activity);
            $totalRewardsPeople = count($rewardsList);
            $res = $this->getLevelList($totalRewardsPeople);
            return $this->success([
                'id' => $activity->id,
                'name' => $activity->name,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'style' => $activity->style,
                'number' => $activity->number,
                'app_share_pic' => $activity->app_share_pic,
                'app_share_title' => $activity->app_share_title,
                'rule_title' => $activity->rule_title,
                'rule_content' => $activity->rule_content,
                'share_user' => $this->getShareUser(),
                'rewards_list' => $rewardsList,
                'total_people_rewards' => $totalRewardsPeople,
                'level_list' => $res['level_list'],
                'main' => $res['main']
            ]);
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @return FissionActivity|array|\yii\db\ActiveRecord
     * @throws \Exception
     * 需要分成三种情况1--参与别人的活动 2--自身开启的活动 3--未参与的活动
     */
    protected function getActivity()
    {
        /** @var FissionActivity $activity */
        $activity = FissionActivity::find()->with('rewards')
            ->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'status' => 1
            ])->andWhere([
                '<=', 'start_time', mysql_timestamp()
            ])->andWhere([
                '>', 'end_time', mysql_timestamp()
            ])->one();
        /** @var FissionActivityLog $newActivity */
        if ($this->id) {
            // 当前没有正在进行中的活动，用户分享活动已到期
            if (!$activity) {
                throw new \Exception('该活动已到期，无法参与');
            }
            $this->rewards = $activity->rewards;
            // 当前正在进行中的活动不是分享的活动，则用户重新发起活动
            if ($this->id != $activity->id) {
                $this->user_id = 0;
                return $activity;
            }
            $newActivity = FissionActivityLog::find()
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'activity_id' => $this->id,
                    'user_id' => $this->user_id
                ])->one();
            // 当前正在进行中的活动，分享的活动不存在或者已被删除时，用户重新发起活动
            if (!$newActivity) {
                $this->user_id = 0;
            }
        } else {
            $this->user_id = 0;
            if (!$activity) {
                throw new \Exception('活动未开始');
            }
            $newActivity = FissionActivityLog::find()
                ->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'activity_id' => $activity->id,
                    'user_id' => \Yii::$app->user->id
                ])->one();
            // 当前正在进行中的活动，如果用户没有参与，则返回活动详情
            if (!$newActivity) {
                $this->rewards = $activity->rewards;
                return $activity;
            }
            // 当前正在进行中的活动，如果用户参与，则返回活动快照
            $activity = new FissionActivity();
            $activity->attributes = Json::decode($newActivity->activity, true);
            $this->rewards = [];
            $rewards = Json::decode($newActivity->rewards, true);
            foreach ($rewards as $reward) {
                $model = new FissionActivityReward();
                $model->attributes = $reward;
                $this->rewards[] = $model;
            }
        }
        return $activity;
    }

    protected function getShareUser()
    {
        $shareUser = null;
        if ($this->user_id) {
            $user = User::findOne(['id' => $this->user_id, 'mall_id' => \Yii::$app->mall->id]);
            if ($user) {
                $shareUser = [
                    'id' => $user->id,
                    'nickname' => $user->nickname,
                    'avatar' => $user->userInfo->avatar
                ];
            }
        }
        return $shareUser;
    }

    /**
     * @return array
     */
    protected function getLevelList($totalRewardsPeople)
    {
        $main = [];
        $list = [];
        $couponIds = [];
        $goodsIds = [];
        $cardIds = [];
        foreach ($this->rewards as $reward) {
            if ($reward->type > 0 && $reward->level == 'main') {
                $list[$reward->type] = [
                    'people_number' => $reward->people_number,
                    'type' => $reward->type,
                    'is_unlock' => false,
                    'status' => $reward->status,
                    'min_number' => $reward->min_number,
                    'model_id' => $reward->model_id,
                    'goods' => null,
                    'card' => null,
                    'coupon' => null,
                ];
            }
            if ($reward->type == 0) {
                $main = [
                    'status' => $reward->status,
                    'min_number' => $reward->min_number,
                    'max_number' => $reward->max_number,
                    'send_type' => $reward->send_type,
                    'model_id' => $reward->model_id,
                    'coupon' => null,
                ];
            }
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
        $main['coupon'] = isset($couponList[$main['model_id']]) ? $couponList[$main['model_id']] : null;
        ksort($list);
        $totalPeople = 0;
        foreach ($list as &$item) {
            $item['people_number'] += $totalPeople;
            $totalPeople = $item['people_number'];
            if ($totalPeople <= $totalRewardsPeople) {
                $item['is_unlock'] = true;
            }
            switch ($item['status']) {
                case 'goods':
                    $item['goods'] = $goodsList[$item['model_id'] . '-' . $item['attr_id']] ?? null;
                    break;
                case 'coupon':
                    $item['coupon'] = $couponList[$item['model_id']] ?? null;
                    break;
                case 'card':
                    $item['card'] = $cardList[$item['model_id']] ?? null;
                    break;
                case 'integral':
                    $item['min_number'] = intval($item['min_number']);
                    $item['max_number'] = intval($item['max_number']);
                    break;
                default:
            }
        }
        unset($item);
        return [
            'main' => $main,
            'level_list' => array_values($list)
        ];
    }

    /**
     * @param FissionActivity $activity
     * @return array
     */
    protected function getRewardsList($activity)
    {
        return [];
    }
}
