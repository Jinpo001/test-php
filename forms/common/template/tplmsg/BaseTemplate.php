<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/7/5
 * Time: 16:23
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\tplmsg;

use app\forms\common\template\TemplateForm;
use app\forms\common\template\TemplateSend;
use app\models\Model;
use app\models\User;

/**
 * @property User $user
 * @property TemplateForm $templateForm
 */
abstract class BaseTemplate extends Model
{
    public $user;
    public $page;
    protected $templateTpl;
    public $dataKey; // 获取data的键值，目前主要是微信订阅消息用到

    /**
     * @return mixed
     * @throws \Exception
     */
    abstract public function msg();

    /**
     * @return mixed
     * @throws \Exception
     * 测试发送模板消息
     */
    abstract public function test();

    /**
     * @return array
     * @throws \Exception
     * 发送模板消息
     */
    public function send()
    {
        try {
            $template = new TemplateSend();
            $template->user = $this->user;
            $template->page = $this->page;
            $template->data = $this->msg();
            $template->dataKey = $this->dataKey;
            $template->templateTpl = $this->templateTpl;
            $template->tplClass = $this;
            return $template->sendTemplate();
        } catch (\Exception $exception) {
            \Yii::error($exception);
        }
    }

    public function adaptive($platform)
    {
        if (method_exists($this, $platform)) {
            return $this->$platform();
        } else {
            return $this->msg();
        }
    }

    protected function wechat()
    {
        return array_merge($this->wechatFirst(), $this->wechatMsg(), $this->wechatRemark());
    }

    protected function wechatMsg()
    {
        return $this->msg();
    }

    protected function wechatFirst()
    {
        return [
            'first' => [
                'value' => \Yii::$app->mall->name,
                'color' => '#333333',
            ],
        ];
    }

    protected function wechatRemark()
    {
        return [
            'remark' => [
                'value' => '系统通知',
                'color' => '#333333',
            ],
        ];
    }
}
