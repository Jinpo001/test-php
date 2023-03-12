<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/15
 * Time: 5:23 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\components;


use app\core\response\ApiCode;
use app\forms\common\CommonOptionP;
use app\helpers\PluginHelper;
use app\plugins\fission\forms\common\CommonOption;
use app\plugins\fission\forms\common\CommonSetting;
use yii\base\Action;

class SettingDataAction extends Action
{
    public function run()
    {
        $setting = CommonSetting::getInstance()->getSetting();
        $setting = $this->addDefaultImg($setting);
        $setting['poster'] = (new CommonOptionP())->poster($setting['poster'], CommonOption::getPosterDefault());

        $setting['default_poster'] = (new CommonOptionP())->poster(CommonOption::getPosterDefault(), CommonOption::getPosterDefault());
        $setting['style_pic'] = $this->getStylePic($setting['style']);
        \Yii::$app->response->data = [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }

    public function addDefaultImg($setting)
    {
        $iconUrl = PluginHelper::getPluginBaseAssetsUrl('fission') . '/img';
        $defaultImg = [
            'default_bd_pic' => $iconUrl . '/bg.png',
            'default_activity_bg_pic' => $iconUrl . '/image-normal.png',
        ];
        return array_merge($setting, $defaultImg);
    }

    public function getStylePic($style)
    {
        $iconUrl = PluginHelper::getPluginBaseAssetsUrl('fission') . '/img';
        $list = [
            1 => $iconUrl . '/style_1.gif',
            2 => $iconUrl . '/style_2.gif',
            3 => $iconUrl . '/style_3.gif',
            4 => $iconUrl . '/style_4.gif',
        ];
        return $list[$style];
    }
}
