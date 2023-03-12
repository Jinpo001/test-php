<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\basic;

use app\forms\common\card\CommonCard;
use app\models\GoodsCards;
use app\models\UserCard;

class Card extends BaseAbstract implements Base
{
    public function exchange(&$message, &$reward)
    {
        try {
            $goodsCards = GoodsCards::find()->where([
                'id' => $this->config['card_id'],
                'is_delete' => 0,
            ])->one();
            if (!$goodsCards) {
                throw new \Exception('卡劵不存在');
            }
            $card_num = $this->config['card_num'];
            $remark = sprintf('兑换码%s兑换', $this->codeModel->code);

            $card = new CommonCard();
            $card->user = $this->user;
            $card->user_id = $this->user->id;
            /** @var GoodsCards $userCard */
            $userCard = $card->receive($goodsCards, 0, 0, $remark, $card_num);
            if (!$userCard) {
                throw new \Exception('库存不足或无效卡券');
            }
            //记录userCardId
            $count = UserCard::find()->where(['remark' => $remark])->select('id')->column();
            $reward['user_card_id'] = $count;

            return !is_null($userCard->id);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}