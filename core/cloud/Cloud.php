<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/7 10:05:00
 */


namespace app\core\cloud;


use yii\base\Component;

/**
 * @property CloudCollect $collect
 * @property CloudTemplate $template
 */
class Cloud extends Component
{
    public $classVersion = '4.2.10';

    /** @var CloudBase $auth */
    public $base;

    /** @var CloudAuth $auth */
    public $auth;

    /** @var CloudPlugin $plugin */
    public $plugin;

    /** @var CloudUpdate $update */
    public $update;

    /** @var CloudWxapp $wxapp */
    public $wxapp;

    /** @var CloudCollect $collect */
    public $collect;

    /** @var CloudTemplate $template */
    public $template;

    public function init()
    {
        parent::init();
        $this->base = new CloudBase();
        $this->auth = new CloudAuth();
        $this->plugin = new CloudPlugin();
        $this->update = new CloudUpdate();
        $this->wxapp = new CloudWxapp();
        $this->collect = new CloudCollect();
        $this->template = new CloudTemplate();
    }
}
