<?php

namespace app\plugins\wholesale\forms\common;


use app\forms\common\CommonOption;
use app\helpers\PluginHelper;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class SettingForm extends Model
{
    public static $setting;

    public function search()
    {
        if (self::$setting) {
            return self::$setting;
        }

        $setting = ArrayHelper::toArray(CommonOption::get('wholesale_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN));

        $default = $this->getDefault();
        if ($setting) {
            $diffSetting = array_diff_key($default, $setting);
            $setting = array_merge($setting, $diffSetting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int)$item : $item;
            }, $setting);
        } else {
            $setting = $default;
        }

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        if (!isset($permissionFlip['vip_card'])) {
            $setting['svip_status'] = -1;
        }
        if (!isset($permissionFlip['share'])) {
            $setting['is_share'] = -1;
        }

        $setting['vip_show_limit'] = $setting['vip_show_limit'] ? json_decode($setting['vip_show_limit'], true) : $setting['vip_show_limit'];

        self::$setting = $setting;
        return $setting;
    }

    private function getDefault()
    {
        try {
            $iconUrl = PluginHelper::getPluginBaseAssetsUrl('wholesale') . '/img';
        } catch (\Exception $exception) {
            $iconUrl = '';
        }
        return [
            'is_share' => 0,
            'is_territorial_limitation' => 0,
            'is_coupon' => 1,
            'is_member_price' => 1,
            'is_integral' => 1,
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'is_vip_show' => 0,
            'vip_show_limit' => [],
            'banner' => $iconUrl . '/banner.png',
            'default_banner' => $iconUrl . '/banner.png',
            'is_full_reduce' => 1,
        ];
    }
}
