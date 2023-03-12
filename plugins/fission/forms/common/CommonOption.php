<?php

namespace app\plugins\fission\forms\common;

use app\helpers\PluginHelper;

class CommonOption
{
    public static function getPosterDefault()
    {
        if (!isset(\Yii::$app->request->hostInfo)) {
            return [];
        }

        $iconBaseUrl = PluginHelper::getPluginBaseAssetsUrl('fission') . '/img/';

        return [
            'bg_pic' => [
                'url' => $iconBaseUrl . 'poster_bg.png',
            ],
            'head' => [
                'is_show' => '1',
                'size' => 47,
                'top' => 10,
                'left' => 10,
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 20,
                'top' => 25,
                'left' => 80,
                'text' => '用户昵称',
                'color' => '#353535',
                'file_type' => 'text',
            ],
            'remake' => [
                'is_show' => '1',
                'font' => 18,
                'top' => 222,
                'left' => 125,
                'width' => 205,
                'text' => '现金红包等你抢',
                'color' => '#ffffff',
                'file_type' => 'text',
            ],
            'desc' => [
                'is_show' => '1',
                'font' => 14,
                'top' => 632,
                'left' => 103,
                'width' => 205,
                'text' => '长按识别小程序码领取红包',
                'color' => '#ffffff',
                'file_type' => 'text',
            ],
            'qr_code' => [
                'is_show' => '1',
                'size' => 120,
                'top' => 500,
                'left' => 127,
                'type' => '1',
                'file_path' => '',
                'file_type' => 'image',
            ],
        ];
    }
}