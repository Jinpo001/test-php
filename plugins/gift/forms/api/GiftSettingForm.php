<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\plugins\gift\forms\api;

use app\core\response\ApiCode;
use app\forms\common\template\TemplateList;
use app\plugins\gift\forms\common\CommonGift;
use app\models\Model;

class GiftSettingForm extends Model
{
    public function getList()
    {
        $setting = CommonGift::getSetting();
        $data['title'] = $setting['title'];
        $data['type'] = $setting['type'];
        $data['bless_word'] = $setting['bless_word'];
        $data['ask_gift'] = $setting['ask_gift'];
        $data['explain'] = $setting['explain'];
        $data['background'] = $setting['background'];
        $data['theme'] = $setting['theme'];
        $data['theme_color'] = $this->getTheme($setting['theme']);
        $data['big_gift_pic'] = $setting['poster']['pic']['pic_url'];
        $data['template_message_captain_gift_convert'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
            'gift_convert'
        ]);
        $data['template_message_captain_gift_form_user'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
            'gift_form_user'
        ]);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => $data
        ];
    }

    public function getTheme($themeId)
    {
        $theme = [
            1 => [
                'main' => '#ddb766',
                'secondary' => '#f0ebd8',
                'main_text' => '#ffffff',
                'secondary_text' => '#ffffff'
            ],
            2 => [
                'main' => '#ff547b',
                'secondary' => '#ffe6e8',
                'main_text' => '#ffffff',
                'secondary_text' => '#ffffff'
            ],
            3 => [
                'main' => '#ff4544',
                'secondary' => '#ffdada',
                'main_text' => '#ffffff',
                'secondary_text' => '#ff4544'
            ],
            4 => [
                'main' => '#7783ea',
                'secondary' => '#e9ebff',
                'main_text' => '#ffffff',
                'secondary_text' => '#ffffff'
            ],
            5 => [
                'main' => '#63be72',
                'secondary' => '#e1f4e3',
                'main_text' => '#ffffff',
                'secondary_text' => '#63be72'
            ],
            6 => [
                'main' => '#4a90e2',
                'secondary' => '#dbe9f9',
                'main_text' => '#ffffff',
                'secondary_text' => '#ffffff'
            ]
        ];
        $color = $theme[$themeId['id']];
        $main = $this->hex2rgb($color['main']);
        $mainP = $this->hex2rgb($color['main'], 0.2);
        return [
            'color' => $color['main'],
            'background' => $color['main'],
            'border' => $color['main'],
            'main_text' => $color['main_text'],
            'background_o' => $this->hex2rgb($color['main'], 0.1),
            'background_p' => $mainP,
            'background_q' => $this->hex2rgb($color['main'], 0.8),
            'background_gradient' => "linear-gradient(to bottom, {$color['main']}, {$color['secondary']})",
            'background_gradient_btn' => "linear-gradient(to left, {$main}, {$this->hex2rgb($color['main'], 0.7)})",
            'shadow' => $mainP
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
