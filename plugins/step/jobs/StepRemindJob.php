<?php

namespace app\plugins\step\jobs;

use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\jobs\BaseJob;
use app\models\User;
use app\plugins\step\forms\common\CommonStep;
use app\plugins\step\forms\common\StepNoticeInfo;
use app\plugins\step\models\StepUser;
use yii\base\Component;
use yii\queue\JobInterface;

class StepRemindJob extends BaseJob implements JobInterface
{
    public $model;

    public function execute($queue)
    {
        try {
            $this->setRequest();
            $this->checkRemindTimeOut();
        } catch (\Exception $e) {
            \Yii::error($e);
        }
    }

    /**
     * 提醒处理
     * @param $event
     */
    public function checkRemindTimeOut()
    {
        $cache = \Yii::$app->cache;
        $mall_id = $this->model->mall_id;
        $setting = CommonStep::getSetting($mall_id);

        $remind_at = $setting['remind_at'];
        if (date('H:i') < $remind_at) {
            return true;
        }

        $key = 'step_daily' . $mall_id;
        $stepDaily = $cache->get($key);
        if ($stepDaily && $stepDaily == date('Y-m-d')) {
            return true;
        }

        $stepUser = StepUser::find()->alias('s')->where([
                's.mall_id' => $mall_id,
                's.is_delete' => 0,
                's.is_remind' => 1,
            ])->innerJoinWith('user')->all();

        if (!$stepUser) {
            return false;
        }
        foreach ($stepUser as $item) {
            try {
                $this->sendTemplate($item);
                $this->sendSmsToUser($item->user);

            } catch (\Exception $exception) {
                \Yii::error($exception->getMessage());
            }
        }

        $cache->set($key, date('Y-m-d'));
        $id = \Yii::$app->queue->delay(strtotime("$remind_at + 1 day") - time())->push(new StepRemindJob([
             'model' => $this->model,
        ]));
    }

    public function sendTemplate($item)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(StepNoticeInfo::TPL_NAME)->send([
                'title' => '步数兑换',
                'time' => mysql_timestamp(),
                'remark' => '每日兑换提醒！',
                'user' => $item->user,
                'page' => 'plugins/step/index/index'
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
            $messageService->tplKey = StepNoticeInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
