<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/22
 * Time: 15:03
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;


use app\events\ShareEvent;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\order_pay_template\AudiResultInfo;
use app\forms\common\template\TemplateList;
use app\models\Share;

class BecomeShareHandle extends HandlerBase
{
    public function register()
    {
        \Yii::$app->on(HandlerRegister::BECOME_SHARE, function ($event) {
            /* @var ShareEvent $event */
            if ($event->share->status == 1 || $event->share->status == 2) {
                $this->sendTemplate($event);
                $this->sendSmsToUser($event->share);
            }
            return true;
        });
    }

    public function sendTemplate($event)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(AudiResultInfo::TPL_NAME)->send([
                'page' => 'pages/share/index/index',
                'user' => $event->share->user,
                'reviewProject' => '分销商审核',
                'result' => $event->share->getStatusText($event->share->status),
                'nickname' => $event->share->user->nickname,
                'time' => $event->share->updated_at
            ]);
        } catch (\Exception $exception) {
            \Yii::warning('--分销商审核结果发送失败--');
            \Yii::warning($exception);
        }
    }

    /**
     * @param Share $share
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($share)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$share->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $share->user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => [
                    '分销商', '通过'
                ]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($share->user);
            $messageService->tplKey = AudiResultInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
