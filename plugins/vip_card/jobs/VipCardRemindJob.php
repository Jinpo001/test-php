<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/21
 * Time: 18:01
 */

namespace app\plugins\vip_card\jobs;

use app\forms\common\template\TemplateList;
use app\models\Mall;
use app\models\User;
use app\plugins\vip_card\forms\common\MsgService;
use app\plugins\vip_card\models\RemindInfo;
use app\plugins\vip_card\models\VipCardUser;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class VipCardRemindJob extends BaseObject implements JobInterface
{

    /**@var VipCardUser $user**/
    public $user;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        try {
            if (strtotime($this->user->end_time) - time() > 60*60*25) {
                return ;
            }
            $mall = Mall::findOne(['id' => $this->user->mall_id]);
            $user = User::findOne(['id' => $this->user->user_id, 'mall_id' => $this->user->mall_id, 'is_delete' => 0]);
            \Yii::$app->setMall($mall);

            MsgService::sendSms($user,$this->user->image_main_name ? $this->user->image_main_name : $this->user->image_name);

            $this->sendTempalte($user);

        } catch (\Exception $exception) {
            \Yii::error("续费提醒失败:" . $exception->getMessage() . $exception->getFile() . $exception->getLine());
        }
    }

    public function sendTempalte($user)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(RemindInfo::TPL_NAME)->send([
                'endTime' => date('Y-m-d', strtotime($this->user->end_time)),
                'cardName' => $this->user->image_name,
                'user' => $user,
                'page' => 'plugins/vip_card/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }
}
