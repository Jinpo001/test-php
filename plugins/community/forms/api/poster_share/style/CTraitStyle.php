<?php


namespace app\plugins\community\forms\api\poster_share\style;

use app\forms\api\poster\style\TraitStyle;
use app\plugins\community\forms\common\CommonSetting;


trait CTraitStyle
{
    use TraitStyle;

    public $activity_id;
    public $middleman_id;

    public function cGetBg($type)
    {
        $common = CommonSetting::getCommon();
        $setting = $common->getSetting();

        if (intval($type) === 3) {
            $this->poster_arr = [$this->setBg($setting['image_bg'])];
            return $this;
        } else {
            return $this->getBg();
        }
    }

    public function getNote($left = 0, $top = 0)
    {
        $middleman = $this->other['middleman'];
        extract($middleman);
        $address = ($city === $province) ? $city . $district : $province . $city . $district;
        $text = sprintf('我在%s%s发现了一个超划算的团购！', $address, $location);
        $text = self::autowrap(30 / self::FONT_FORMAT, 0, $this->font_path, $text, 480, 2);
        array_push($this->poster_arr, $this->setText($text, $left, $top, 30, '#353535'));
        return $this;
    }

    public function getRemeke($left = 0, $top = 0)
    {
        $info = $this->other;

        extract($info['middleman']);
        $address = ($city === $province) ? $city . $district : $province . $city . $district;
        {
            $name = self::autowrap(22 / self::FONT_FORMAT, 0, $this->font_path, $name, 220, 1);
            array_push($this->poster_arr
                , $this->setText('团长：', $left, $top, 22, '#353535')
                , $this->setImage($avatar, 33, 33, $left + 68, $top - 10)
                , $this->setText($name, $left + 68 + 33 + 10, $top, 22, '#353535')
            );
        }
        {
            array_push($this->poster_arr
                , $this->setText('结束时间：' . $info['activity']['end_at'], $left, $top + 35, 22, '#353535')
            );
        }
        {
            $address = self::autowrap(22 / self::FONT_FORMAT, 0, $this->font_path, '提货地址：' . $address . $detail, 330, 2);
            array_push($this->poster_arr
                , $this->setText($address, $left, $top + 35 + 35, 22, '#4e4e4e')
            );
        }
        return $this;
    }

    public function getGoodsList($top = 0)
    {
        switch (count($this->other['activity']['goods_list'])) {
            case 0:
                die('ERROR');
            case 1:
                $top += 0;
                return $this->one($top);
            case 2:
                $top += 10;
                return $this->two($top);
            case 3:
                $top += 10;
                return $this->three($top);
            case 4:
                $top += 30;
                return $this->four($top);
            default:
                return $this->six($top);
        }
    }

    public function one($top)
    {
        $goodsList = $this->other['activity']['goods_list'];
        $goods = current($goodsList);
        $goodsName = self::autowrap(28 / self::FONT_FORMAT, 0, $this->font_path, $goods['name'], 580, 2);
        $price = '￥' . $goods['price'];
        $original_price = '￥' . $goods['original_price'];

        $n = any2eucjp(28 / self::FONT_FORMAT, 0, $this->font_path, $goodsName);
        $n_width = $n[2] - $n[0];
        $n_height = abs($n[7] - $n[1]);

        $p = any2eucjp(28 / self::FONT_FORMAT, 0, $this->font_path, $price);
        $p_width = $p[2] - $p[0];

        $o = any2eucjp(26 / self::FONT_FORMAT, 0, $this->font_path, $original_price);
        $o_width = $o[2] - $o[0];

        array_push($this->poster_arr
            , $this->setImage($goods['cover_pic'], 360, 360, (750 - 360) / 2, $top)
            , $this->setText($goodsName, (750 - $n_width) / 2, $top + 360 + 24, 28, '#353535')
            , $this->setText($price, (750 - $p_width) / 2, $top + 360 + 24 + $n_height + 30, 28, '#ff4544')
        );
        if ($goods['original_price'] > 0) {
            array_push($this->poster_arr
                , $this->setText($original_price, (750 - $o_width) / 2, $top + 360 + 24 + $n_height + 30 + 34, 26, '#999999')
                , $this->setLine([(750 - $o_width) / 2 - 5, 456 + $n_height + $top], [(750 - $o_width) / 2 + $o_width + 10, 456 + $n_height + $top], '#c9c9c9')
            );
        }
        return $this;
    }

    public function two($top)
    {
        $goodsList = $this->other['activity']['goods_list'];
        $left = 65;
        while ($goods = current($goodsList)) {
            $goodsName = self::autowrap(24 / self::FONT_FORMAT, 0, $this->font_path, $goods['name'], 256, 2);
            $price = '￥' . $goods['price'];
            $original_price = '￥' . $goods['original_price'];

            $n = any2eucjp(24 / self::FONT_FORMAT, 0, $this->font_path, $goodsName);
            $n_width = $n[2] - $n[0];
            $n_height = abs($n[7] - $n[1]);

            $p = any2eucjp(24 / self::FONT_FORMAT, 0, $this->font_path, '￥' . $goods['price']);
            $p_width = $p[2] - $p[0];

            $o = any2eucjp(22 / self::FONT_FORMAT, 0, $this->font_path, '￥' . $goods['original_price']);
            $o_width = $o[2] - $o[0];

            $localPath = \Yii::$app->basePath . '/web/statics/img/app/community/';
            array_push($this->poster_arr
                , $this->setImage($localPath . 't_box.png', 290, 450, $left, $top)
                , $this->setImage($goods['cover_pic'], 290, 290, $left, $top)
                , $this->setText($goodsName, $left + (290 - $n_width) / 2, $top + 290 + 12, 24, '#353535')
                , $this->setText($price, $left + (290 - $p_width) / 2, $top + 290 + 12 + $n_height + 24, 24, '#ff4544')
            );
            if ($goods['original_price'] > 0) {
                array_push($this->poster_arr
                    , $this->setText($original_price, $left + (290 - $o_width) / 2, $top + 290 + 12 + $n_height + 48, 22, '#999999')
                    , $this->setLine([$left + (290 - $o_width) / 2 - 5, $top + 290 + 12 + $n_height + 55], [$left + (290 - $o_width) / 2 + $o_width + 10, $top + 290 + 12 + $n_height + 55], '#c9c9c9')
                );
            }
            $left += 290 + 40;
            next($goodsList);
        }
        return $this;
    }

    public function three($top)
    {
        $goodsList = $this->other['activity']['goods_list'];

        while ($goods = current($goodsList)) {
            $goodsName = self::autowrap(26 / self::FONT_FORMAT, 0, $this->font_path, $goods['name'], 450, 2);
            $price = '￥' . $goods['price'];
            $original_price = '￥' . $goods['original_price'];
//            $n = any2eucjp(26 / self::FONT_FORMAT, 0, $this->font_path, $goodsName);
//            $n_height = abs($n[7] - $n[1]);

            $o = any2eucjp(22 / self::FONT_FORMAT, 0, $this->font_path, '￥' . $goods['original_price']);
            $o_width = $o[2] - $o[0];

            array_push($this->poster_arr
                , $this->setImage($goods['cover_pic'], 150, 150, 65, $top)
                , $this->setText($goodsName, 65 + 150 + 24, $top, 26, '#353535')
                , $this->setText($price, 65 + 150 + 24, $top + 100, 28, '#ff4544')
            );
            if ($goods['original_price'] > 0) {
                array_push($this->poster_arr
                    , $this->setText($original_price, 65 + 150 + 24 + 5, $top + 132, 22, '#999999')
                    , $this->setLine([65 + 150 + 24 , $top + 138],
                        [65 + 150 + 24 + $o_width + 15, $top + 138], '#c9c9c9')
                );
            }
            next($goodsList);
            $top += 150 + 20;
        }
        return $this;
    }

    public function four($top)
    {
        $goodsList = $this->other['activity']['goods_list'];
        while ($goods = current($goodsList)) {
            $goodsName = self::autowrap(24 / self::FONT_FORMAT, 0, $this->font_path, $goods['name'], 480, 2);
            $price = '￥' . $goods['price'];
            $original_price = '￥' . $goods['original_price'];

            $n = any2eucjp(24 / self::FONT_FORMAT, 0, $this->font_path, $goodsName);
            $n_height = abs($n[7] - $n[1]);

            $o = any2eucjp(26 / self::FONT_FORMAT, 0, $this->font_path, $original_price);
            $o_width = $o[2] - $o[0];

            array_push($this->poster_arr
                , $this->setImage($goods['cover_pic'], 120, 120, 65, $top)
                , $this->setText($goodsName, 65 + 120 + 20, $top, 24, '#353535')
                , $this->setText($price, 65 + 120 + 20, $top + 70, 24, '#ff4544')
            );
            if ($goods['original_price'] > 0) {
                array_push($this->poster_arr
                    , $this->setText($original_price, 65 + 120 + 20, $top + 100, 20, '#999999')
                    , $this->setLine([65 + 120 + 20, $top + 108],[65 + 120 + 20 + $o_width, $top + 108], '#c9c9c9')
                );
            }
            next($goodsList);
            $top += 120 + 20;
        }
        return $this;
    }

    public function six($top)
    {
        $s = 1;
        $left = 65;
        $temp_top = $top;
        $goodsList = $this->other['activity']['goods_list'];
        $goodsList = array_splice($goodsList, 0, 6);
        while ($goods = current($goodsList)) {
            $goodsName = self::autowrap(24 / self::FONT_FORMAT, 0, $this->font_path, $goods['name'], 166, 2);
            $price = '￥' . $goods['price'];
            $original_price = '￥' . $goods['original_price'];

//            $n = any2eucjp(24 / self::FONT_FORMAT, 0, $this->font_path, $goodsName);
//            $n_height = abs($n[7] - $n[1]);

            $o = any2eucjp(20 / self::FONT_FORMAT, 0, $this->font_path, $original_price);
            $o_width = $o[2] - $o[0];

            array_push($this->poster_arr
                , $this->setImage($goods['cover_pic'], 120, 120, $left, $top)
                , $this->setText($goodsName, $left + 120 + 14, $top, 24, '#353535')
                , $this->setText($price, $left + 120 + 14, $top + 70, 24, '#ff4544')
            );
            if ($goods['original_price'] > 0) {
                array_push($this->poster_arr
                    , $this->setText($original_price, $left + 120 + 14, $top + 100, 20, '#999999')
                    , $this->setLine([$left + 120 + 14 - 5, $top + 108],
                        [$left + 120 + 14 + $o_width + 10,  $top + 108], '#c9c9c9')
                );
            }
            next($goodsList);
            $top += 120 + 20;
            $left = 65 + intval($s / 3) * 300;
            if (fmod($s, 3) == 0) {
                $top = $temp_top;
            }
            $s++;
        }
        return $this;
    }
}