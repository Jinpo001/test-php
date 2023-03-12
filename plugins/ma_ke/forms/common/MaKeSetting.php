<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\ma_ke\forms\common;


use app\models\Mall;
use app\models\Model;
use app\models\Option;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 */
class MaKeSetting extends Model
{
    private static $instance;

    private static $keyName = 'ma_ke_setting';

    protected function __construct() {}

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new MaKeSetting();
        }

        return self::$instance;
    }

    public function getSetting() 
    {
        $setting = \app\forms\common\CommonOption::get(self::$keyName, \Yii::$app->mall->id, Option::GROUP_ADMIN);
        $defaultSetting = $this->getDefault();

        if ($setting) {
            $setting = ArrayHelper::toArray($setting);
            $setting = array_merge($defaultSetting, $setting);

            $setting = array_map(function ($item) {
                return is_numeric($item) ? (int)$item : $item;
            }, $setting);
        } else {
            $setting = $defaultSetting;
        }

        return $setting;
    }

    public function getKeyName()
    {
        return self::$keyName;
    }

    public function getDefault()
    {
        return [
            'id' => null,
            'status' => 0,
            'app_id' => '',
            'token' => '',
            'domain' => '',
        ];
    }
}
