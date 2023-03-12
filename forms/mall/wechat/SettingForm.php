<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/24
 * Time: 3:41 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\mall\wechat;

use app\core\response\ApiCode;
use app\forms\common\wechat\WechatFactory;
use app\helpers\ArrayHelper;
use app\models\Model;
use app\models\WechatConfig;
use app\models\WechatWxmpprograms;
use luweiss\Wechat\Wechat;
use luweiss\Wechat\WechatException;

class SettingForm extends Model
{
    public $appid;
    public $appsecret;

    public function rules()
    {
        return [
            [['appid', 'appsecret'], 'required'],
            [['appid', 'appsecret'], 'trim'],
            [['appid', 'appsecret'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'appid' => '微信公众平台AppId',
            'appsecret' => '微信公众平台appSecret',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $wechatConfig = WechatFactory::getConfigByMallId(\Yii::$app->mall->id);
        if (!$wechatConfig) {
            $wechatConfig = new WechatConfig();
            $wechatConfig->mall_id = \Yii::$app->mall->id;
            $wechatConfig->is_delete = 0;
        }
        try {
            if ($this->appid || $this->appsecret) {
                $wechat = new Wechat([
                    'appId' => $this->appid,
                    'appSecret' => $this->appsecret,
                ]);
                $wechat->getAccessToken(true);
            }
        } catch (WechatException $e) {
            if ($e->getRaw()['errcode'] == '40013') {
                $message = '微信公众平台AppId有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
            if ($e->getRaw()['errcode'] == '40125') {
                $message = '微信公众平台appSecret有误(' . $e->getRaw()['errmsg'] . ')';
                return [
                    'code' => ApiCode::CODE_ERROR,
                    'msg' => $message,
                ];
            }
        }
        $wechatConfig->version = 2;
        $wechatConfig->attributes = $this->attributes;
        if (!$wechatConfig->save()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function getDetail()
    {
        $wechatConfig = WechatConfig::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'version' => 2]);
        $third = WechatWxmpprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'version' => 2]);
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $data['has_third_permission'] = in_array('wxmpplatform', $permission);
        if (!$wechatConfig && !$third) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '信息暂未配置',
                'data' => $data
            ];
        }
        $data['detail'] = $wechatConfig ? ArrayHelper::filter($wechatConfig->attributes, [
            'appid', 'appsecret', 'name', 'logo', 'qrcode'
        ]) : [];
        $data['third'] = $third;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $data
        ];
    }
}
