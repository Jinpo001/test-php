<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/14
 * Time: 11:26 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\core\mail\SendMail;
use app\models\Mall;
use yii\queue\JobInterface;

class MailJob extends BaseJob implements JobInterface
{
    /**
     * @var SendMail $class
     */
    public $class;

    public $view;
    public $params;

    public function execute($queue)
    {
        $this->setRequest();
        $this->class->job($this->view, $this->params);
    }
}
