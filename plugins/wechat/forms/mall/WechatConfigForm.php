<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/21
 * Time: 3:46 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\wechat\WechatFactory;
use app\helpers\ArrayHelper;
use app\plugins\wechat\forms\Model;

class WechatConfigForm extends Model
{
    public $name;
    public $logo;
    public $qrcode;

    public function rules()
    {
        return [
            [['name', 'logo', 'qrcode'], 'required'],
            [['name', 'logo', 'qrcode'], 'trim'],
            [['name', 'logo', 'qrcode'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '公众号名称',
            'logo' => '公众号logo',
            'qrcode' => '公众号二维码'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $wechatConfig = WechatFactory::getConfigByMallId(\Yii::$app->mall->id);

        if (!$wechatConfig) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '公众号暂未配置，请先配置公众号基础配置'
            ];
        }

        $wechatConfig->attributes = $this->attributes;

        if (!$wechatConfig->save()) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败',
            ];
        }
        $indexForm = new IndexForm();
        $data = $indexForm->getPath();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功',
            'data' => $data
        ];
    }

    public function getDetail()
    {
        $wechatConfig = WechatFactory::getConfigByMallId(\Yii::$app->mall->id);
        $data['detail'] = $wechatConfig ? ArrayHelper::filter($wechatConfig->attributes, [
            'name', 'logo', 'qrcode'
        ]) : [];
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $data['has_third_permission'] = in_array('wxmpplatform', $permission);
        $data['third'] = WechatFactory::getThirdByMall(\Yii::$app->mall->id);
        $data['is_setting'] = WechatFactory::isSetting();
        $indexForm = new IndexForm();
        $data = array_merge($data, $indexForm->getPath());
        return $this->success($data);
    }
}
