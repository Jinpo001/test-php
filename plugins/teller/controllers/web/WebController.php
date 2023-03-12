<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/11/7 18:17
 */


namespace app\plugins\teller\controllers\web;

use app\plugins\Controller;
use app\plugins\teller\controllers\web\filter\LoginFilter;

class WebController extends Controller
{
    public function init()
    {
        if (property_exists(\Yii::$app, 'appIsRunning') === false) {
            exit('property not found.');
        }
    }

    public $layout = '/main';

    public function behaviors()
    {
        return [
            'loginFilter' => [
                'class' => LoginFilter::class,
                'safeRoutes' => [
                    'plugin/teller/web/passport/login',
                    'plugin/teller/web/passport/setting',
                ],
            ]
        ];
    }

    public function render($view, $params = [])
    {
        if (mb_stripos($view, '@') !== 0 && mb_stripos($view, '/') !== 0) {
            $view = '@app/plugins/' . $this->module->id . '/views/web/' . mb_strtolower($this->id) . '/' . $view;
        }
        return parent::render($view, $params);
    }
}
