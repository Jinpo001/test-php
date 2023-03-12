<?php

namespace app\plugins\fission\jobs;

use app\jobs\BaseJob;
use app\models\GoodsAttr;
use app\plugins\fission\forms\common\CommonEcard;
use yii\helpers\Json;
use yii\queue\JobInterface;
use app\plugins\fission\models\FissionRewardLog;

class GoodsAddStock extends BaseJob implements JobInterface
{
    public $attr_id;
    public $reward_id;

    public function execute($queue)
    {
        $this->setRequest();
        try {
            $exists = FissionRewardLog::find()->where([
                'id' => $this->reward_id,
                'is_exchange' => 0,
            ]);
            if ($exists) {
                (new GoodsAttr())->updateStock(1, 'add', $this->attr_id);
                $rewardLog = FissionRewardLog::findOne($this->reward_id);
                $reward = Json::decode($rewardLog->reward, true);
                CommonEcard::getCommon()->refundEcard([
                    'type' => 'order_token',
                    'sign' => 'fission',
                    'num' => 1,
                    'goods_id' => $reward['model_id'],
                    'order_token' => $rewardLog->token
                ]);
            }
        } catch (\Exception $e) {
            \Yii::error('红包墙过期回滚库存失败：' . $e->getMessage());
        }
    }
}
