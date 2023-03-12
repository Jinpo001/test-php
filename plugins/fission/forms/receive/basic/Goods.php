<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\basic;

use app\models\GoodsAttr;
use app\plugins\fission\forms\common\CommonEcard;
use app\plugins\fission\jobs\GoodsAddStock;

class Goods extends BaseAbstract implements Base
{
    public function exchange(&$message,&$log)
    {
        try {
            if ($this->rewardLog->expire_time) {
                $s = strtotime('1 day', 0);
                \Yii::$app->queue->delay(bcmul($s, $this->rewardLog->expire_time))->push(new GoodsAddStock([
                    'attr_id' => $this->reward['attr_id'],
                    'reward_id' => $this->rewardLog->id,
                ]));
            }
            $this->rewardLog->token = \Yii::$app->security->generateRandomString();
            $this->rewardLog->save();
            CommonEcard::getCommon()->setEcardFission($this->rewardLog);
            (new GoodsAttr())->updateStock(1, 'sub', $this->reward['attr_id']);
            \Yii::error('红包墙商品无法手动兑换');
            return true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}
