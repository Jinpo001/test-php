<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/12/25
 * Time: 14:13
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\api\message;


use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\models\TemplateRecord;

class TemplateForm extends Model
{
    public $templateTpl;

    public function rules()
    {
        return [
            [['templateTpl'], 'trim'],
            [['templateTpl'], 'string'],
        ];
    }

    public function getList()
    {
        try {
            $platform = \Yii::$app->appPlatform;
            $list = TemplateList::getInstance()->getTestTemplateList($platform);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $list
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '平台不支持模板消息',
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $res = TemplateList::getInstance()
                ->getTemplateClass($this->templateTpl)
                ->test([
                    'user' => \Yii::$app->user->identity
                ]);
            $isDone = true;

            while ($isDone) {
                if (\Yii::$app->queue->isDone($res['queueId'])) {
                    $templateRecord = TemplateRecord::findOne(['token' => $res['token']]);
                    $data = [
                        'status' => $templateRecord->status,
                        'msg' => $templateRecord->status == 1 ? '发送成功' : $templateRecord->error,
                    ];
                    $isDone = false;
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $data
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '发送失败'
            ];
        }
    }
}
