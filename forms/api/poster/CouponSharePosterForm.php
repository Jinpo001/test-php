<?php


namespace app\forms\api\poster;


use app\forms\api\coupon\CouponDetailForm;
use app\forms\common\grafika\GrafikaOption;

class CouponSharePosterForm extends GrafikaOption implements BasePoster
{
    public $coupon_id;

    public function rules()
    {
        return [
            [['coupon_id'], 'integer'],
        ];
    }


    public function getOption($option)
    {
        $file_path = \Yii::$app->basePath . '/web/statics/img/mall/poster/';
        return [
            'bg_pic' => [
                'url' => $option['poster_bg'],
                'is_show' => '1',
            ],
            'head' => [
                'is_show' => '1',
                'size' => 130,
                'top' => 66,
                'left' => (750 - 130) / 2,
                'file_path' => self::head($this),
                'file_type' => 'image',
            ],
            'nickname' => [
                'is_show' => '1',
                'font' => 28 / 1.48,
                'top' => 66 + 28 + 130,
                'left' => 59,
                'color' => '#ffffff',
                'text' => $option['nickname'],
                'file_type' => 'text',
            ],

            'priceR' => [
                'is_show' => '1',
                'font' => 34 / 1.48,
                'top' => 438,
                'left' => 0,
                'color' => '#ffffff',
                'text' => $option['type'] === 1 ? '折' : '￥',
                'file_type' => 'text',
            ],
            'price' => [
                'is_show' => '1',
                'font' => 58 / 1.48,
                'top' => 420,
                'left' => 130,
                'color' => '#ffffff',
                'text' => $option['min_price'],
                'file_type' => 'text',
            ],

            'between' => [
                'is_show' => '1',
                'font' => 33 / 1.48,
                'top' => 410,
                'left' => 286,
                'color' => '#353535',
                'text' => $option['min_price'] > 0 ? sprintf('满%s可用', $option['min_price']) : '无门槛使用',
                'file_type' => 'text',
            ],
            'appointType' => [
                'is_show' => '1',
                'font' => 29 / 1.48,
                'top' => 464,
                'left' => 286,
                'color' => '#353535',
                'text' => $option['appoint_type'],
                'file_type' => 'text',
            ],
            'timeBg' => [
                'is_show' => '1',
                'width' => 480,
                'height' => 40,
                'top' => 884,
                'left' => 0,
                'file_path' => $file_path . 'share_bg.png',
                'file_type' => 'image',
            ],
            'time' => [
                'is_show' => '1',
                'font' => 24 / 1.48,
                'top' => 884 + 11,
                'left' => 0,
                'color' => '#ffffff',
                'text' => $option['expire_type'] == 1 ? sprintf('有效期：领取后%s天内有效', $option['expire_day'])
                    : sprintf('有效期：%s-%s', $option['begin_time'], $option['end_time']),
                'file_type' => 'text',
            ],
            'qrCode' => [
                'is_show' => '1',
                'size' => 240,
                'top' => 968,
                'left' => (750 - 240) / 2,
                'file_path' => $option['qrcode'],
                'file_type' => 'image',
            ],

        ];
    }

    public function get()
    {
        $form = new CouponDetailForm();
        $form->attributes = \Yii::$app->request->get();
        $return = $form->poster();

        if ($return['code'] !== 0) {
            throw new \Exception($return['msg']);
        }

        $info = $return['data'];
        $option = $this->getOption($info);
        {
            $n = any2eucjp($option['nickname']['font'], 0, $this->font_path, $option['nickname']['text']);
            $n_width = $n[2] - $n[0];
            $option['nickname']['left'] = (750 - $n_width) / 2;
        }

        {
            if ($info['type'] == 1) {
                $option['price']['left'] = 90;
                $option['price']['text'] = $info['discount'];
                $p = any2eucjp($option['price']['font'], 0, $this->font_path, $option['price']['text']);
                $p_width = $p[2] - $p[1];
                $option['priceR']['left'] = 90 + $p_width + 12;
            } else {
                $option['priceR']['left'] = 90;
                $option['price']['text'] = $info['sub_price'];
            }
        }

        {
            $t = any2eucjp($option['time']['font'], 0, $this->font_path, $option['time']['text']);
            $t_width = $t[2] - $t[0];
            $option['time']['left'] = (750 - $t_width) / 2;
            $option['timeBg']['width'] = $t_width + 48;
            $option['timeBg']['left'] = $option['time']['left'] - 24;
        }

        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }
        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}