<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/14
 * Time: 10:11 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;

use app\forms\common\message\MessageService;
use app\models\Mall;
use yii\queue\JobInterface;

class MessageServiceJob extends BaseJob implements JobInterface
{
    /**
     * @var MessageService $messageService
     */
    public $messageService;

    /**
     * @var Mall $mall
     */
    public $mall;

    public $appPlatform;

    public function execute($queue)
    {
        $this->setRequest();
        $mall = Mall::findOne($this->mall->id);
        \Yii::$app->setMall($mall);
        \Yii::$app->setAppPlatform($this->appPlatform);
        $this->messageService->job();
    }
}
