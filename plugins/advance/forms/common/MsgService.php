<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/9/5
 * Time: 10:19
 */

namespace app\plugins\advance\forms\common;

use app\forms\common\template\order_pay_template\TailMoneyInfo;
use Overtrue\EasySms\Message;
use app\forms\common\CommonAppConfig;
use app\forms\common\template\TemplateList;

class MsgService
{
    public static function sendTpl($user, $event)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(TailMoneyInfo::TPL_NAME)->send([
                'price' => $event->price,
                'goodsName' => $event->goods,
                'user' => $user,
                'page' => 'plugins/advance/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    public static function sendSms($user, $goodsName)
    {
        try {
            $smsConfig = CommonAppConfig::getSmsConfig(0);
            if (!isset($smsConfig['tailMoney']) || !isset($smsConfig['tailMoney']['template_id']) || !isset($smsConfig['tailMoney']['name'])) {
                throw new \Exception('商品预定插件短信未设置正确');
            }
            $data[$smsConfig['tailMoney']['name']] = $goodsName;
            $message = new Message([
                'template' => $smsConfig['tailMoney']['template_id'],
                'data' => $data
            ]);
            $user->mobile && \Yii::$app->sms->module('mall')->send($user->mobile, $message);
        } catch (\Exception $exception) {
            \Yii::error('=====商品预定插件短信短信通知失败=====');
            \Yii::error($exception);
        }
    }
}