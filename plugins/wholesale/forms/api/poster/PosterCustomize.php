<?php

namespace app\plugins\wholesale\forms\api\poster;

use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\helpers\PluginHelper;
use app\models\Model;
use app\plugins\wholesale\Plugin;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitQrcode($class)
    {
        return [
            ['id' => $class->goods->id, 'user_id' => \Yii::$app->user->id],
            240,
            'plugins/wholesale/goods/goods'
        ];
    }

    public function traitPrice($model, $left, $top, $has_center, $color)
    {
        $font_path = \Yii::$app->basePath . '/web/statics/font/st-heiti-light.ttc';

        $text = $this->setText('', $left, $top + 28, 30, $color);
        $t = any2eucjp($text['font'], 0, $font_path, $text['text']);
        $t_width = $t[2] - $t[0];
        if ($model->other['min_price'] == $model->other['max_price']) {
            $price = $model->other['min_price'];
        } else {
            $price = $model->other['min_price'] . '~' . $model->other['max_price'];
        }

        $mark_width = 28;
        $mark = $this->setText('￥', 0, $top + 25, 32, $color);
        $price = $this->setText($price, 0, $top + 15, 52, $color);

        if ($has_center) {
            $left = 0;
            $g = any2eucjp($price['font'], 0, $font_path, $price['text']);
            $g_width = $g[2] - $g[0];
            $left = (750 - $g_width - $t_width - $mark_width) / 2;
        }

        $text['left'] = $left;
        $mark['left'] = $left + $t_width + 3;
        $price['left'] = $left + $t_width + 3 + $mark_width;
        return [
            $text,
            $mark,
            $price,
        ];
    }

    public function traitMultiMapContent()
    {
        $plugin = new Plugin();

        $image_url = PluginHelper::getPluginBaseAssetsUrl($plugin->getName()) . '/img/wholesale-poster-new.png';
        $image = [
            'file_type' => self::TYPE_IMAGE,
            'top' => 0,
            'left' => 0,
            'height' => 110,
            'width' => 120,
            'image_url' => $image_url,
        ];
        return [
            $image,
        ];
    }
}
