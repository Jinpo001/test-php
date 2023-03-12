<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/12/15 15:09
 */


namespace app\forms\api;


use app\core\exceptions\ClassNotFoundException;
use app\forms\api\app_platform\Transform;
use app\forms\common\AppImg;
use app\forms\common\CommonAppConfig;
use app\forms\common\config\UserCenterConfig;
use app\forms\common\share\CommonShareConfig;
use app\forms\mall\recharge\RechargePageForm;
use app\forms\mall\share\ShareCustomForm;
use app\forms\mall\theme_color\ThemeColorForm;
use app\models\Model;
use app\plugins\diy\Plugin;

class ConfigForm extends Model
{
    public function search()
    {
        $mall = \Yii::$app->mall->getMallSetting();
        $mall['setting']['web_service_url'] = urlencode($mall['setting']['web_service_url']);
        $mall['setting']['latitude_longitude'] = $mall['setting']['latitude'] . ',' . $mall['setting']['longitude'];

        $mall['setting']['current_customer_service'] = [];
        $cList = $mall['setting']['customer_service_list'];
        if (count($cList)) {
            $num = mt_rand(0, count($cList) - 1);
            $mall['setting']['current_customer_service'] = $cList[$num];
        }

        $plugin = $this->getPluginConfig();
        $barTitle = CommonAppConfig::getBarTitle();

        $navbar = CommonAppConfig::getNavbar();
        $navbar = Transform::getInstance()->transformNavbar($navbar);

        $res = [
            'code' => 0,
            'data' => [
                'mall' => $mall,
                'navbar' => $navbar,
                'user_center' => UserCenterConfig::getInstance()->getApiUserCenter(),
                'plugin' => $plugin,
                'copyright' => CommonAppConfig::getCoryRight(),
                '__wxapp_img' => AppImg::search(),
                'share_setting' => CommonShareConfig::config(),
                'share_setting_custom' => (new ShareCustomForm())->getData()['data'],
                'recharge_page_custom' => (new RechargePageForm())->getSetting(),
                'auth_page' => $this->getDefaultAuthPage(),
                'cat_style' => CommonAppConfig::getAppCatStyle(),
                'bar_title' => $barTitle,
                'theme_color' => $this->getThemeColor()
            ],
        ];

        return $res;
    }

    private function getPluginConfig()
    {
        $data = [];
        $list = \Yii::$app->plugin->getList();
        foreach ($list as $item) {
            try {
                $data[$item->name] = \Yii::$app->plugin->getPlugin($item->name)->getAppConfig();
            } catch (ClassNotFoundException $exception) {
            }
        }
        return $data;
    }

    public function getDefaultAuthPage()
    {
        try {
            /* @var Plugin $plugin */
            $plugin = \Yii::$app->plugin->getPlugin('diy');
            $result = $plugin->getAlonePage('auth');
        } catch (ClassNotFoundException $exception) {
            $pages = CommonAppConfig::getDefaultPageList();
            $result = $pages['auth'];
        }
        return $result;
    }

    private function getThemeColor()
    {
        $themeList = (new ThemeColorForm())->getThemeData();
        $color = [];
        $key = '';
        foreach ($themeList as $item) {
            if ($item['is_select']) {
                $color = $item['color'];
                $key = $item['key'];
                break;
            }
        }
        $main = $this->hex2rgb($color['main']);
        $mainAll = $this->hex2rgb($color['main'], 1);
        $mainHalf = $this->hex2rgb($color['main'], 0.5);
        $mainS = $this->hex2rgb($color['main'], 0.7);
        $secondary = $this->hex2rgb($color['secondary']);
        $secondaryS = $this->hex2rgb($color['secondary'], 0.7);
        $border = "border-bottom-color: transparent;border-left-color: transparent;border-right-color: transparent;";
        return [
            'color' => $color['main'],
            'background' => $color['main'],
            'border' => $color['main'],
            'main_text' => $color['main_text'],
            'secondary_text' => $color['secondary_text'],
            'border_m' => "border-color: {$color['main']};" . $border,
            'background_o'  => $this->hex2rgb($color['main'], 0.1),
            'background_p'  => $this->hex2rgb($color['main'], 0.2),
            'background_l'  => $this->hex2rgb($color['main'], 0.35),
            'background_q'  => $this->hex2rgb($color['main'], 0.8),
            'background_gradient' => "linear-gradient(140deg, {$color['main']}, {$color['secondary']})",
            'background_gradient_l' => "linear-gradient(to right, {$mainAll}, {$mainHalf})",
            'background_gradient_o' => "linear-gradient(to right, {$mainAll}, {$mainS})",
            'background_s_gradient_o' => "linear-gradient(to right, {$secondary}, {$secondaryS})",
            'background_gradient_btn' => "linear-gradient(to right, {$main}, {$mainS})",
            'background_s_gradient_btn' => "linear-gradient(to right, {$secondary}, {$secondaryS})",
            'background_s' => $color['secondary'],
            'border_s' => $color['secondary'],
            'key' => $key,
        ];
    }

    private function hex2rgb($hexColor, $alpha = 2)
    {
        $rgbArr = hex2rgb($hexColor);
        if ($alpha <= 1) {
            return sprintf('rgba(%s, %s, %s, %s)', $rgbArr['r'], $rgbArr['g'], $rgbArr['b'], $alpha);
        } else {
            return sprintf('rgb(%s, %s, %s)', $rgbArr['r'], $rgbArr['g'], $rgbArr['b']);
        }
    }
}
