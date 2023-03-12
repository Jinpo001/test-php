<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\wxapp\forms\wx_app_config;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappConfig;
use app\plugins\wxapp\models\WxappWxminiprograms;
use luweiss\Wechat\Wechat;
use luweiss\Wechat\WechatException;

class WxAppConfigEditForm extends Model
{
    public $appid;
    public $appsecret;
    public $id;

    public function rules()
    {
        return [
            [['appid', 'appsecret'], 'string'],
            [['id'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid' => '小程序AppId',
            'appsecret' => '小程序appSecret',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        /**@var WxappConfig $wxAppConfig * */
        $wxAppConfig = WxappConfig::find()
            ->where(['mall_id' => \Yii::$app->mall->id])
            ->with('service')
            ->one();
        if (!$wxAppConfig) {
            $wxAppConfig = new WxappConfig();
        }
        $third = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if (!$third) {
            try {
                if (!$this->appid) {
                    throw new \Exception('小程序AppId有误');
                }
                if (!$this->appsecret) {
                    throw new \Exception('小程序appSecret有误');
                }
                $wechat = new Wechat(
                    [
                        'appId' => $this->appid,
                        'appSecret' => $this->appsecret,
                    ]
                );
                $wechat->getAccessToken(true);
            } catch (WechatException $e) {
                if ($e->getRaw()['errcode'] == '40013') {
                    $message = '小程序AppId有误(' . $e->getRaw()['errmsg'] . ')';
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $message,
                    ];
                }
                if ($e->getRaw()['errcode'] == '40125') {
                    $message = '小程序appSecret有误(' . $e->getRaw()['errmsg'] . ')';
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $message,
                    ];
                }
            }
        }

        try {
            $t = \Yii::$app->db->beginTransaction();
            $wxAppConfig->mall_id = \Yii::$app->mall->id;
            if (!$third) {
                $wxAppConfig->appid = $this->appid;
                $wxAppConfig->appsecret = $this->appsecret;
            }
            $res = $wxAppConfig->save();
            if ($res) {
                $t->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功',
                ];
            }

            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
