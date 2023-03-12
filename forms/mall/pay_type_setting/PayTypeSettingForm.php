<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/11/4
 * Time: 16:16
 */

namespace app\forms\mall\pay_type_setting;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\models\PayType;

class PayTypeSettingForm extends Model
{
    public $wxapp;
    public $wechat;
    public $mobile;

    public function rules()
    {
        return [
            [['wxapp', 'wechat', 'mobile'], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $res = CommonOption::set(
            Option::NAME_PAYMENT_PAY_TYPE,
            [
                'wxapp' => $this->wxapp,
                'wechat' => $this->wechat,
                'mobile' => $this->mobile
            ],
            \Yii::$app->mall->id,
            Option::GROUP_APP
        );
        if (!$res) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败'
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '保存成功'
        ];
    }

    public function getDetail()
    {
        //权限判断
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $newPermission = [];
        $platform = array_keys($this->getDefault());
        foreach ($platform as $item) {
            if (in_array($item, $permission)) {
                $newPermission[] = $item;
            }
        }
        $option = CommonOption::get(
            Option::NAME_PAYMENT_PAY_TYPE,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            $this->getDefault()
        );
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'permission' => $newPermission,
                'option' => $option
            ],
        ];
    }

    public function getDefault()
    {
        return [
            'wxapp' => [
                'wx' => '',
            ],
            'wechat' => [
                'wx' => '',
                'ali' => '',
            ],
            'mobile' => [
                'wx' => '',
                'ali' => ''
            ]
        ];
    }
}
