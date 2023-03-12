<?php

namespace app\plugins\fission\forms\api;

use app\core\response\ApiCode;
use app\forms\common\grafika\GrafikaOption;
use app\plugins\fission\forms\common\CommonOption;

class PosterForm extends GrafikaOption
{
    public $activity_id;

    public function rules()
    {
        return [
            [['activity_id'], 'required'],
            [['activity_id'], 'integer'],
        ];
    }

    public function poster()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $this->get()
        ];
    }


    public function get()
    {
        $setting = \app\plugins\fission\forms\common\CommonSetting::getInstance()->getSetting();
        //空白图
        empty($setting['poster']['bg_pic']['url']) && $setting['poster']['bg_pic']['url'] = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png';
        $option = $this->optionDiff($setting['poster'], CommonOption::getPosterDefault());
        isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;
        isset($option['remake']) && $option['remake']['text'] = self::autowrap($option['remake']['font'], 0, $this->font_path, $option['remake']['text'], $option['desc']['width']);
        isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }
        isset($option['qr_code']) && $option['qr_code']['file_path'] = self::qrcode($option, [
            [
                'activity_id' => $this->activity_id,
                'invite_user_id' => \Yii::$app->user->id,
                'user_id' => \Yii::$app->user->id
            ],
            240,
            'plugins/fission/index/index'
        ], $this);

        isset($option['head']) && $option['head']['file_path'] = self::head($this);
        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}