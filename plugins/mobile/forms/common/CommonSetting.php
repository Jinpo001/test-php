<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/29
 * Time: 5:00 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\forms\common;

use app\forms\common\CommonOption;
use app\helpers\ArrayHelper;
use app\plugins\mobile\forms\Model;

class CommonSetting extends Model
{
    /* @var CommonSetting $instance */
    public static $instance;
    public $mall;
    public const REGISTER_SETTING = 'h5_register_setting';
    public const H5_CONTACT = 'h5_contact';

    /**
     * @param null $mall
     * @return CommonSetting
     */
    public static function getCommon($mall = null)
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

    protected function getConfig()
    {
        return [
            [
                'key' => 'is_show_agreement',
                'name' => '注册协议开关',
                'default' => 1,
            ],
            [
                'key' => 'agreement',
                'name' => '协议内容',
                'default' => '',
            ],
            [
                'key' => 'agreement_name',
                'name' => '协议标题',
                'default' => ''
            ],
            [
                'key' => 'declare',
                'name' => '隐私内容',
                'default' => ''
            ],
            [
                'key' => 'declare_name',
                'name' => '隐私标题',
                'default' => ''
            ],
            [
                'key' => 'register_img',
                'name' => '注册页背景图',
                'default' => ''
            ],
            [
                'key' => 'login_img',
                'name' => '登录页背景图',
                'default' => ''
            ],
        ];
    }

    /**
     * @param array $filters
     * @return array|false
     * 获取默认值
     */
    protected function getDefault($filters = [])
    {
        return $this->getColumn('default', $filters);
    }

    /**
     * @param array $filters
     * @return array
     * 获取实际配置值
     */
    public function getRegisterSetting($filters = [])
    {
        $default = $this->getDefault($filters);
        $setting = CommonOption::get(self::REGISTER_SETTING, $this->mall->id, 'plugin', $default);
        $setting = ArrayHelper::merge($default, $setting);
        return ArrayHelper::filter($setting, $filters);
    }

    /**
     * @param array $filters
     * @return array|false
     * 获取名称
     */
    public function getName($filters = [])
    {
        return $this->getColumn('name', $filters);
    }

    protected function getColumn($name, $filters)
    {
        $res = array_column($this->getConfig(), $name, 'key');
        return ArrayHelper::filter($res, $filters);
    }
}
