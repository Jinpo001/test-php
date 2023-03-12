<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/29
 * Time: 13:22
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\check_in\jobs;


use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\jobs\BaseJob;
use app\models\Mall;
use app\models\User;
use app\plugins\check_in\forms\common\Common;
use app\plugins\check_in\forms\common\CommonInfo;
use app\plugins\check_in\models\CheckInConfig;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @property Mall $mall
 * @property CheckInConfig $config
 */
class RemindJob extends BaseJob implements JobInterface
{
    public $mall;

    public function execute($queue)
    {
        $this->setRequest();
        \Yii::warning('--签到提醒--');
        $this->mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($this->mall);
        $common = Common::getCommon($this->mall);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $config = $common->getConfig();
            if (!$config) {
                throw new \Exception('签到未开放');
            }
            if ($config->status == 0) {
                throw new \Exception('签到未开启');
            }
            if ($config->is_remind == 0) {
                throw new \Exception('签到未开启提醒功能');
            }
            $time = time();
            $configTime = strtotime($config->time);
            // 提醒时间没有到，重新添加定时任务
            if ($configTime - $time > 0) {
                return ;
            }

            $checkInUserAll = $common->getCheckInUserByRemind();

            foreach ($checkInUserAll as $checkInUser) {
                try {
                    $this->sendTemplate($checkInUser->user);
                    $this->sendSmsToUser($checkInUser->user);

                    $common->addCheckInUserRemind([
                        'user_id' => $checkInUser->user_id,
                        'mall_id' => $checkInUser->mall_id,
                        'is_delete' => 0,
                        'date' => date('Y-m-d H:i:s'),
                        'is_remind' => 1,
                    ]);
                } catch (\Exception $exception) {
                    \Yii::warning($exception);
                    continue;
                }
            }
            $common->addRemindJob();
            $t->commit();
        } catch (\Exception $exception) {
            $common->addRemindJob();
            \Yii::warning($exception);
            $t->rollBack();
        }
    }

    public function sendTemplate($user)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(CommonInfo::TPL_NAME)->send([
                'user' => $user,
                'page' => 'plugins/check_in/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception);
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
            $messageService->tplKey = CommonInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
