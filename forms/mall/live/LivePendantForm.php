<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\mall\live\CommonLive;
use app\models\LivePendantSetting;
use app\models\Model;

class LivePendantForm extends Model
{
    public function rules()
    {
        return [];
    }

    public function getSetting()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $defaultSetting = $this->getDefault();
            $setting = LivePendantSetting::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->one();
            if ($setting) {
                $setting = json_decode($setting->extra_attributes, true);
                foreach ($defaultSetting as $key => $value) {
                    if (is_array($value)) {
                        $setting[$key] = isset($setting[$key]) ? $setting[$key] : $value;
                    } else if (is_float($value)) {
                        $setting[$key] = isset($setting[$key]) ? (float)price_format($setting[$key]) : $value;
                    } else if (is_numeric($value)) {
                        $setting[$key] = isset($setting[$key]) ? (int)$setting[$key] : $value;
                    } else {
                        $setting[$key] = isset($setting[$key]) ? $setting[$key] : $value;
                    }
                }
            } else {
                $setting = $defaultSetting;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'setting' => $setting
                ]
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function getDefault()
    {
        return [
            'is_open' => 0
        ]; 
    }
}
