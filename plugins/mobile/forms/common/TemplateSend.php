<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/29
 * Time: 3:22 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\forms\common;

use app\forms\common\template\TemplateSender;
use app\plugins\mobile\Plugin;

class TemplateSend extends TemplateSender
{
    private $mallId;
    protected $plugin;
    public $is_need_form_id = false;

    public function init()
    {
        parent::init();
        $this->plugin = new Plugin();
    }

    public function sendTemplate($arg = array())
    {
    }
}
