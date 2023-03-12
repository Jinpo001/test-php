<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\lottery\forms\common;

use app\forms\common\CommonOptionP;
use app\forms\common\version\Compatible;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\lottery\models\LotterySetting;

class CommonLottery extends Model
{
    public static $setting;

    /**
     * @return LotterySetting|null
     */
    public static function getSetting()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $setting = LotterySetting::findOne(['mall_id' => \Yii::$app->mall->id]);

        $cs_prompt_default = !isset(\Yii::$app->request->hostInfo) ? '' : PluginHelper::getPluginBaseAssetsUrl('lottery') . '/img/';
        $default = [
            'type' => 0,
            'title' => '',
            'rule' => '',
            'send_type' => ['express', 'offline'],
            'goods_poster' => CommonOption::getPosterDefault(),
            'payment_type' => ['online_pay'],
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'cs_status' => 0,
            'cs_prompt_pic' => $cs_prompt_default . 'prompt.png',
            'cs_wechat' => [],
            'cs_wechat_flock_qrcode_pic' => [],
            'bg_pic' => $cs_prompt_default . 'bg-pic.png',
            'bg_color' => '#ff4544',
            'bg_color_type' => 'pure',
            'bg_gradient_color' => '#ff4544',
        ];

        if ($setting) {
            $func = function ($key) use ($setting, $default) {
                $data = $setting[$key];
                if ($data && \yii\helpers\BaseJson::decode($data) && is_array(\yii\helpers\BaseJson::decode($data))) {
                    return \yii\helpers\BaseJson::decode($data);
                } else {
                    return $default[$key];
                }
            };
            $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);
            $setting['payment_type'] = $func('payment_type');
            $setting['goods_poster'] = $func('goods_poster');
            $setting['cs_wechat_flock_qrcode_pic'] = $func('cs_wechat_flock_qrcode_pic');
            $setting['cs_wechat'] = $func('cs_wechat');
            $setting['cs_prompt_pic'] = $setting['cs_prompt_pic'] ?: $default['cs_prompt_pic'];

            $getDefault = function ($key) use ($setting, $default) {
                return $setting[$key] ?: $default[$key];
            };
            foreach ($default as $key => $item) {
                if (stripos($key, 'bg_') === 0) {
                    $setting[$key] = $getDefault($key);
                }
            }
        } else {
            $setting = $default;
        }
        self::$setting = $setting;
        return $setting;
    }

    public static function flashUserInfoOne($item, $is_hide = false)
    {
        if (isset($item['user'])) {
            $cKey = 'user';
        } else if (isset($item['childUser'])) {
            $cKey = 'childUser';
        } else {
            return $item;
        }

        $nickname = $item[$cKey]['nickname'];
        if ($is_hide) {
            if (mb_strlen($nickname) > 2) {
                $nickname = mb_substr($nickname, 0, 1);
            }
            $nickname .= '**';
        }
        $item[$cKey] = [
            'id' => $item[$cKey]['id'],
            'nickname' => $nickname,
            'userInfo' => [
                'avatar' => $item[$cKey]['userInfo']['avatar'] ?? '',
            ],
        ];
        return $item;
    }

    public static function flashUserInfoAll($data, $is_hide = false)
    {
        foreach ($data as $key => $item) {
            $data[$key] = self::flashUserInfoOne($item, $is_hide);
        }
        return $data;
    }
}
