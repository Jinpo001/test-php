<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\common;


use app\helpers\PluginHelper;
use app\models\Model;
use app\models\Option;
use app\plugins\scan_code_pay\Plugin;
use app\plugins\scan_code_pay\models\ScanCodePaySetting;
use yii\helpers\ArrayHelper;

class CommonScanCodePaySetting extends Model
{
    public function getSetting()
    {
        $setting = \app\forms\common\CommonOption::get('scan_code_pay_setting', \Yii::$app->mall->id, Option::GROUP_ADMIN);
        // 兼容旧数据
        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
        } else {
            $setting = ScanCodePaySetting::find()->where(['mall_id' => \Yii::$app->mall->id])->one();
            if ($setting) {
                $setting = ArrayHelper::toArray($setting);
            }
        }

        $default = $this->getDefault();
        if ($setting) {
            $setting['payment_type'] = $setting['payment_type'] ? json_decode($setting['payment_type'], true) : $default['payment_type'];
            $setting['poster'] = json_decode($setting['poster'], true);

            $diffSetting = array_diff_key($this->getDefault(), $setting);
            $setting = array_merge($setting, $diffSetting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int) $item : $item;
            }, $setting);
        } else {
            $setting = $default;
        }

        $arr = ['is_show', 'size', 'top', 'left', 'type'];
        foreach ($setting['poster'] as $key1 => $item) {
            foreach ($item as $key2 => $item2) {
                if (in_array($key2, $arr)) {
                    $setting['poster'][$key1][$key2] = (int)$item2;
                }
            }
        }

        return $setting;
    }

    public function getDefault()
    {
        $pluginName = (new Plugin())->getName();
        $imageBaseUrl = PluginHelper::getPluginBaseAssetsUrl($pluginName) . '/img';
        return [
            'is_scan_code_pay' => 0,
            'is_clerk' => 1,
            'payment_type' => ['online_pay'],
            'is_share' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'share_type' => 1,
            'share_commission_first' => 0,
            'share_commission_second' => 0,
            'share_commission_third' => 0,
            'poster' => [
                'bg_pic' => [
                    'url' => $imageBaseUrl . '/poster_bg.png',
                    'is_show' => 1
                ],
                'qr_code' => [
                    'is_show' => 1,
                    'size' => 120,
                    'top' => 265,
                    'left' => 115,
                    'type' => 1,
                    'file_type' => 'image',
                ],
            ],
        ];
    }
}