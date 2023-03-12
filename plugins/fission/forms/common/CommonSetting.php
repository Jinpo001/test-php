<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/15
 * Time: 5:15 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\common;

use app\forms\common\CommonOption as Option;
use app\models\Mall;
use app\plugins\fission\forms\Model;

class CommonSetting extends Model
{
    /*
     * @var self $instance
     */
    public static $instance;

    /**
     * @var Mall $mall
     */
    public $mall;

    public static function getInstance($mall = null)
    {
        if (!$mall) {
            $mall = \Yii::$app->mall;
        }
        if (self::$instance && self::$instance->mall->id == $mall->id) {
            return self::$instance;
        }
        self::$instance = new self();
        self::$instance->mall = $mall;
        return self::$instance;
    }

    public function getDefault()
    {
        return [
            'poster' => CommonOption::getPosterDefault(),
            'bg_pic' => '', // 背景图
            'custom' => '', // 自定义内容
            'custom_color' => '#ffffff', // 文本颜色
            'contact_list' => [], // 客服微信列表 qrcode--二维码  name--微信号
            'style' => 1, // 红包样式
            'activity_bg_pic' => '', // 红包墙背景图
            'activity_bg_style' => 'pure', // 红包墙下半部分样式 pure--纯色 gradient--渐变
            'activity_bg_color' => '#990f18', // 红包墙下半部分颜色
            'activity_bg_gradient_color' => '#990f18', // 红包墙下半部分渐变色
        ];
    }

    public static $setting;

    public function getSetting()
    {
        if (self::$setting) {
            return self::$setting;
        }
        $default = $this->getDefault();
        $option = Option::get('fission_setting', $this->mall->id, 'plugin', $default);
        self::$setting = $this->checkDefault($default, $option);
        return self::$setting;
    }

    /**
     * @param $default
     * @param $option
     * @return mixed
     *
     */
    public function checkDefault($default, $option)
    {
        foreach ($default as $index => $value) {
            if (isset($option[$index])) {
                $default[$index] = $option[$index];
            }
        }
        return $default;
    }
}
