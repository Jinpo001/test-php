<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\basic;

use app\forms\common\card\CommonCard;
use app\models\GoodsCards;
use app\models\UserCard;

class Card extends BaseAbstract implements Base
{
    public function exchange(&$message, &$log)
    {
        try {
            $goodsCards = GoodsCards::find()->where([
                'id' => $this->reward['model_id'],
                'is_delete' => 0,
            ])->one();
            if (!$goodsCards) {
                throw new \Exception('卡劵不存在');
            }
            $remark = sprintf('红包墙兑换(%s)', $this->rewardLog->id);
            $card = new CommonCard();
            $card->user = $this->user;
            $card->user_id = $this->user->id;
            /** @var GoodsCards $goodsCards */
            $userCard = $card->receive($goodsCards, 0, 0, $remark, 1);
            if (!$userCard) {
                throw new \Exception('库存不足或无效卡券');
            }

            //记录userCardId
            $user_card_id = UserCard::find()->where(['remark' => $remark])->select('id')->column();
            $log->result_id = current($user_card_id);
            return !is_null($userCard->id);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}