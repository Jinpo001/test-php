<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/2
 * Time: 9:44 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\common;

use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateSender;
use app\plugins\wechat\forms\mall\WechatTemplateForm;
use app\plugins\wechat\models\WechatTemplate;
use app\plugins\wechat\Plugin;

/**
 * Class TemplateSend
 * @package app\plugins\wechat\forms\common
 * @property Plugin $plugin
 */
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
        $this->mallId = $arg['user']->mall_id;
        $template = $this->plugin->getWechatTemplate();
        if (isset($arg['templateId']) && $arg['templateId']) {
            $templateId = $arg['templateId'];
        } else {
            if (!isset($arg['templateTpl'])) {
                throw new \Exception('无效的templateTpl或templateId');
            }
            $wxappTemplate = WechatTemplate::findOne([
                'tpl_name' => $arg['templateTpl'],
                'mall_id' => $this->mallId,
            ]);
            if ($wxappTemplate) {
                $templateId = $wxappTemplate->tpl_id;
            } else {
                $templateId = $this->getTemplateId($arg['templateTpl']);
            }
        }
        $sendData = [
            'touser' => PlatformConfig::getInstance()->getPlatformOpenid($arg['user'])['wechat'],
            'template_id' => $templateId,
            'data' => $arg['data'],
        ];
        if (isset($arg['page']) && $arg['page']) {
            $sendData['url'] = rtrim($this->plugin->getWebUri(), '/') . '/' . ltrim($arg['page'], '/');
        }
        return $template->send($sendData);
    }

    public function getTemplateId($templateTpl)
    {

        $model = new WechatTemplateForm();
        return $model->addTemplateOne($templateTpl);
    }
}
