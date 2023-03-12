<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\recharge;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class RechargePageForm extends Model
{
    public $balance_title;
    public $recharge_amount_title;
    public $recharge_explanation_title;
    public $recharge_btn_radius;
    public $recharge_btn_title;
    public $recharge_btn_background;
    public $recharge_btn_color;
    public $is_lottery_open;
    public $lottery_type;
    public $lottery_icon_url;

    public function rules()
    {
        return [
            [['recharge_btn_radius', 'is_lottery_open'], 'integer'],
            [['recharge_btn_radius', 'is_lottery_open'], 'default', 'value' => 0],
            [['balance_title', 'recharge_amount_title', 'recharge_explanation_title', 'recharge_btn_title', 'recharge_btn_background',
                'recharge_btn_color', 'lottery_type', 'lottery_icon_url'], 'string'],
            [['balance_title', 'recharge_amount_title', 'recharge_explanation_title', 'recharge_btn_title', 'recharge_btn_background', 'recharge_btn_color'], 'default', 'value' => ''],
        ];
    }

    public function getDefault()
    {
        $path = isset(\Yii::$app->request->hostInfo) ? \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl : '';
        return [
            'is_lottery_open' => 0,
            'lottery_type' => '',
            'lottery_icon_url' => $path . '/statics/img/app/balance/bg.gif',
            'balance_title' => '余额',
            'recharge_amount_title' => '充值金额',
            'recharge_explanation_title' => '充值说明',
            'recharge_btn_radius' => '40',
            'recharge_btn_title' => '立即充值',
            'recharge_btn_background' => '#FF4544',
            'recharge_btn_color' => '#FFFFFF',
        ];
    }

    //权限判断
    private function getPermission(string $key): bool
    {
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        return array_search($key, $permission) !== false;
    }

    public function get()
    {
        $setting = $this->getSetting();
        $select_list = [];
        foreach ([[
            'label' => '九宫格抽奖',
            'value' => 'pond',
        ], [
            'label' => '刮刮卡',
            'value' => 'scratch',
        ]] as $plugin) {
            $this->getPermission($plugin['value']) && array_push($select_list, $plugin);
        }
        empty($setting['lottery_type']) && count($select_list) === 1 && $setting['lottery_type'] = $select_list[0]['value'];
        empty($select_list) && $setting['is_lottery_open'] = -1;
        return [$setting,$select_list];
//        return [
//            'code' => ApiCode::CODE_SUCCESS,
//            'data' => [
//                'setting' => $setting,
//                'select_list' => $select_list
//            ],
//        ];
    }

    public function getSetting()
    {
        $setting = CommonOption::get(Option::NAME_RECHARGE_PAGE, \Yii::$app->mall->id, Option::GROUP_APP, $this->getDefault());
        $setting = array_merge($this->getDefault(), \yii\helpers\ArrayHelper::toArray($setting));
        $setting['is_lottery_open'] && !$this->getPermission($setting['lottery_type']) && $setting['is_lottery_open'] = '0';
        return $setting;
    }

    public function post()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        if ($this->is_lottery_open == 1 && !$this->getPermission($this->lottery_type) && !in_array($this->lottery_type, ['pond', 'scratch'])) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '选择活动不能为空'
            ];
        }
        $data = [
            'is_lottery_open' => $this->is_lottery_open,
            'lottery_type' => $this->lottery_type,
            'lottery_icon_url' => $this->lottery_icon_url,

            'balance_title' => $this->balance_title,
            'recharge_amount_title' => $this->recharge_amount_title,
            'recharge_explanation_title' => $this->recharge_explanation_title,
            'recharge_btn_radius' => $this->recharge_btn_radius,
            'recharge_btn_title' => $this->recharge_btn_title,
            'recharge_btn_background' => $this->recharge_btn_background,
            'recharge_btn_color' => $this->recharge_btn_color,
        ];

        $option = CommonOption::set(Option::NAME_RECHARGE_PAGE, $data, \Yii::$app->mall->id, Option::GROUP_APP);
        if ($option) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '保存失败'
            ];
        }
    }
}