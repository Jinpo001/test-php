<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\ma_ke\forms\mall;

use app\core\response\ApiCode;
use app\models\CityService;
use app\models\Model;
use app\plugins\ma_ke\forms\common\MaKeSetting;

class MaKeSettingForm extends Model
{
    public function getSetting()
    {
        $cityService = CityService::find()->andWhere([
            'plugin' => 'ma_ke',
            'mall_id' => \Yii::$app->mall->id,
        ])->one();

        if ($cityService) {
            $data = json_decode($cityService->data, true);
            $cityService = [
                'id' => $cityService->id,
                'status' => $cityService->status,
                'app_id' => $data['appkey'],
                'token' => $data['appsecret'],
                'domain' => $data['domain'],
            ];
        } else {
            $cityService = MaKeSetting::getInstance()->getDefault();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $cityService
            ]
        ];
    }
}
