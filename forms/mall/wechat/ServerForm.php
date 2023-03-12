<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/25
 * Time: 9:19 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\forms\common\wechat\WechatFactory;

class ServerForm extends \app\models\Model
{
    public $token;
    public $encodingAESKey;

    public function rules()
    {
        return [
            [['token', 'encodingAESKey'], 'required'],
            [['token', 'encodingAESKey'], 'trim'],
            [['token', 'encodingAESKey'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'token' => '令牌(Token)',
            'encodingAESKey' => '消息加密密钥',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = [
            'token' => $this->token,
            'encodingAESKey' => $this->encodingAESKey
        ];
        CommonOption::set('wechat_server', $list, \Yii::$app->mall->id, 'mall', 0);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function getDetail()
    {
        try {
            $list = WechatFactory::create()->getServer();
            if (!$list) {
                $list = [
                    'token' => '',
                    'encodingAESKey' => ''
                ];
            }
            $apiRoot = str_replace('http://', 'https://', \Yii::$app->request->hostInfo);
            $rootUrl = rtrim(dirname(\Yii::$app->request->baseUrl), '/');
            $list['server'] = $apiRoot . $rootUrl . '/web/msg-notify/city-service.php?mall_id=' . \Yii::$app->mall->id;
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $list
            ];
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }

    public function getString($num = 32)
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => \Yii::$app->security->generateRandomString($num)
        ];
    }
}
