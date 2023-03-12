<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/11
 * Time: 5:00 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\message;

use app\core\newsms\Sms;
use app\forms\common\CommonAppConfig;
use app\forms\common\CommonSms;
use app\jobs\MessageServiceJob;
use app\models\Model;
use app\models\User;
use Overtrue\EasySms\Message;

class MessageService extends Model
{
    /**
     * @var $type string
     * 消息发送的方式 sms--短信  template--模板消息（订阅消息） mail--邮件
     */
    public $type;

    /**
     * @var $content array
     * 消息发送的内容
     */
    public $content;

    /**
     * @var $user User
     * 接收方
     */
    public $user;

    /**
     * @var $tplKey string
     * 消息发送类型
     */
    public $tplKey;

    /**
     * @var $platform string
     * 消息发送平台
     */
    public $platform;

    /**
     * @var $exception array
     * 错误信息
     */
    public $msg = [];

    public function templateSend()
    {
        \Yii::$app->queue3->delay(0)->push(new MessageServiceJob([
            'messageService' => $this,
            'mall' => \Yii::$app->mall,
            'appPlatform' => \Yii::$app->appPlatform
        ]));
    }

    public function job()
    {
        try {
            $this->sms();
        } catch (\Exception $exception) {
            \Yii::warning($exception);
            $this->msg[] = $exception;
        }
        return count($this->msg) > 0 ? false : true;
    }

    /**
     * @return bool
     * @throws \Overtrue\EasySms\Exceptions\GatewayErrorException
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     * 向用户发送短信消息
     */
    public function sms()
    {
        $mobile = $this->user->mobile;
        $content = $this->content;
        if (!$mobile || empty($mobile)) {
            throw new \Exception('手机号不存在，无法发送');
        }
        if (is_string($mobile)) {
            $mobile = [$mobile];
        }
        if (!isset($content['mch_id'])) {
            $content['mch_id'] = 0;
        }
        $smsConfig = CommonAppConfig::getSmsConfig($content['mch_id']);
        if (
            $smsConfig['status'] != 1
            || !isset($smsConfig[$this->tplKey])
            || !isset($smsConfig[$this->tplKey]['template_id'])
            || !$smsConfig[$this->tplKey]['template_id']
        ) {
            throw new \Exception('短信未配置');
        }
        $setting = CommonSms::getCommon()->getSetting();
        $config = $setting[$this->tplKey];
        if ($config['key'] === 'user' && !$this->check($smsConfig['allow_platform'])) {
            throw new \Exception('暂不支持发送短信');
        }
        $data = [];
        foreach ($config['variable'] as $index => $item) {
            if (!isset($smsConfig[$this->tplKey][$item['key']])) {
                throw new \Exception('短信未配置');
            }
            $data[$smsConfig[$this->tplKey][$item['key']]] = $content['args'][$index];
        }
        foreach ($mobile as $item) {
            \Yii::$app->sms->module(Sms::MODULE_MALL)->send($item, new Message([
                'content' => null,
                'template' => $smsConfig[$this->tplKey]['template_id'],
                'data' => $data,
            ]));
        }
        return true;
    }

    protected function check($allowPlatform)
    {
        $platform = explode('_', $this->platform);
        if (count(array_intersect($platform, $allowPlatform)) > 0) {
            return true;
        }
        return false;
    }
}
