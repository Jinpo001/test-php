<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/1/15
 * Time: 14:07
 */


namespace app\plugins\stock\handlers;

use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\order_pay_template\RemoveIdentityInfo;
use app\forms\common\template\TemplateList;
use app\handlers\HandlerBase;
use app\models\User;
use app\plugins\stock\events\StockEvent;
use app\plugins\stock\forms\common\MsgService;
use app\plugins\stock\models\StockUser;

class RemoveStockHandler extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(StockUser::EVENT_REMOVE, function ($event) {
            /**
             * @var StockEvent $event
             */

            try {
                $user = User::findOne([
                    'id' => $event->stock->user_id,
                    'mall_id' => $event->stock->mall_id,
                    'is_delete' => 0
                ]);
                
                try {
                    TemplateList::getInstance()->getTemplateClass(RemoveIdentityInfo::TPL_NAME)->send([
                        'remark' => "股东解除:" . ($event->stock->stockInfo->reason ?? '你的股东身份已被解除'),
                        'time' => date('Y-m-d H:i:s', time()),
                        'user' => $user,
                        'page' => 'plugins/stock/index/index'
                    ]);
                    $this->sendSmsToUser($user, '股东');
                } catch (\Exception $exception) {
                    \Yii::error('模板消息发送: ' . $exception->getMessage());
                }
            } catch (\Exception $exception) {
                \Yii::error("发送股东订阅消息失败");
                \Yii::error($exception);
            }

            return true;
        });
    }

    /**
     * @param User $user
     * @param $remark
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($user, $remark)
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
                'args' => [$remark]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = RemoveIdentityInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
