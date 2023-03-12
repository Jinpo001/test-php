<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/15
 * Time: 14:41
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\handlers;


use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\handlers\HandlerBase;
use app\models\User;
use app\plugins\bargain\events\BargainUserOrderEvent;
use app\plugins\bargain\forms\common\BargainSuccessInfo;

class BargainUserJoinHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::BARGAIN_USER_JOIN, function ($event) {
            /* @var BargainUserOrderEvent $event */
            $bargainUserOrderAll = $event->bargainUserOrderAll;
            $bargainOrder = $event->bargainOrder;
            $totalPrice = 0;
            foreach ($bargainUserOrderAll as $bargainUserOrder) {
                $totalPrice += floatval($bargainUserOrder->price);
            }

            if ($bargainOrder->price - $bargainOrder->min_price <= $totalPrice) {
                $this->sendTemplate($bargainOrder);
                $this->sendSmsToUser($bargainOrder->user);
            }
        });
    }

    public function sendTemplate($bargainOrder)
    {
        try {
            $user = User::findOne(['id' => $bargainOrder->user_id]);
            $pageUrl = 'plugins/bargain/activity/activity?id=' . $bargainOrder->id;

            TemplateList::getInstance()->getTemplateClass(BargainSuccessInfo::TPL_NAME)->send([
                'goodsName' => $bargainOrder->goodsWarehouse->name,
                'price' => $bargainOrder->price . '元',
                'minPrice' => $bargainOrder->min_price . '元',
                'remark' => '已砍至最低价，快来买我吧！！',
                'user' => $user,
                'page' => $pageUrl
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param User $user
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($user)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => []
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = BargainSuccessInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
