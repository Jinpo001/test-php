<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/14
 * Time: 4:18 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\mobile\components;


use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\plugins\mobile\forms\common\CommonSetting;
use yii\base\Action;

class RegisterSettingAction extends Action
{
    public function run()
    {
        $setting = CommonSetting::getCommon()->getRegisterSetting();
        $list = CommonOption::get(CommonSetting::H5_CONTACT, \Yii::$app->mall->id, 'plugin', []);
        $setting['list'] = $list;
        \Yii::$app->response->data = [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $setting
        ];
    }
}
