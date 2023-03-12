<?php


namespace app\forms\api\poster;


use app\forms\api\card\GiveAndReceive;
use app\forms\common\grafika\GrafikaOption;

class CardSharePosterForm extends GrafikaOption implements BasePoster
{
    public $cardId;

    public function rules()
    {
        return [
            [['cardId'], 'integer'],
        ];
    }


    public function getOption($option)
    {
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
                'file_path' => self::avatar(self::head($this)),
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

            'cardPic' => [
                'is_show' => '1',
                'size' => 120,
                'top' => 348 + 45,
                'left' => 76,
                'file_path' => self::avatar(self::saveTempImage($option['pic_url'])),
                'file_type' => 'image',
            ],
            'cardName' => [
                'is_show' => '1',
                'font' => 33 / 1.48,
                'top' => 464,
                'left' => 220,
                'color' => '#353535',
                'text' => $option['name'],
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
        $form = new GiveAndReceive();
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
            $option['cardName']['text'] = self::autowrap($option['cardName']['font'], 0, $this->font_path, $option['cardName']['text'], 330, 2);
            $c = any2eucjp($option['cardName']['font'], 0, $this->font_path, $option['cardName']['text']);
            $c_h = abs($c[7] - $c[1]);
            $option['cardName']['top'] = 348 + (210 - $c_h) / 2;
        }


        $cache = $this->getCache($option);
        if ($cache) {
            return ['pic_url' => $cache . '?v=' . time()];
        }
        $editor = $this->getPoster($option);
        return ['pic_url' => $editor->qrcode_url . '?v=' . time()];
    }
}