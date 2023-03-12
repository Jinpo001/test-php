<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/29
 * Time: 4:15 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\forms\mall;

use app\forms\common\CommonOption;
use app\helpers\ArrayHelper;
use app\plugins\mobile\forms\common\CommonSetting;
use app\plugins\mobile\forms\Model;

/**
 * Class RegisterForm
 * @package app\plugins\mobile\forms\mall
 * @property CommonSetting $setting
 */
class RegisterForm extends Model
{
    /**
     * 注册页面信息：（提供两种登录方式：1、用户名密码登录 2、手机号验证码登录）
     * 用户名
     * 密码
     * 手机号
     * 用户协议内容及名称
     * 隐私权保护声明内容及名称
     * 注册页背景图
     * 登录页背景图
     */
    public $is_show_agreement;
    public $agreement;
    public $agreement_name;
    public $declare;
    public $declare_name;
    public $register_img;
    public $login_img;
    public $list;

    private $setting;

    public function init()
    {
        parent::init();
        $this->setting = CommonSetting::getCommon();
    }

    public function rules()
    {
        return [
            [['agreement', 'agreement_name', 'declare', 'declare_name'], 'required'],
            [['agreement', 'agreement_name', 'declare', 'declare_name', 'register_img', 'login_img'], 'trim'],
            [['agreement', 'agreement_name', 'declare', 'declare_name', 'register_img', 'login_img'], 'string'],
            [['is_show_agreement'], 'integer'],
            [['agreement_name', 'declare_name'], 'string', 'max' => 30],
            ['list', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return $this->setting->getName(array_keys($this->attributes));
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $res = CommonOption::set(
            $this->setting::REGISTER_SETTING,
            $this->attributes,
            \Yii::$app->mall->id,
            'plugin'
        );
        if (!empty($this->list) && count($this->list) > 10) {
            $this->list = array_slice($this->list, 10);
        }
        CommonOption::set($this->setting::H5_CONTACT, $this->list, \Yii::$app->mall->id, 'plugin');
        if (!$res) {
            return $this->fail(['msg' => '保存失败']);
        }
        return $this->success(['msg' => '保存成功']);
    }
}
