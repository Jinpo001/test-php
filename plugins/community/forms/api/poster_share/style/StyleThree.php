<?php

namespace app\plugins\community\forms\api\poster_share\style;

use app\forms\api\poster\common\StyleGrafika;
use app\forms\api\poster\style\BaseStyle;
use app\plugins\community\forms\api\poster_share\style\CTraitStyle as c;

class StyleThree extends StyleGrafika implements BaseStyle
{
    use c;

    public function handleBg()
    {
        $localPath = \Yii::$app->basePath . '/web/statics/img/app/community/';
        array_push($this->poster_arr, $this->setImage($localPath . 'style3-1.png', 702, 1160, 24, 100));
        return $this;
    }

    public function build()
    {
        $this->cGetBg($this->type)
            ->handleBg()
            ->getNote(200, 164 + 14)
            ->getRemeke(360, 1010)
            ->getGoodsList(310);

        if ($file = $this->setFile($this->taskHash())) {
            return ['pic_url' => $file . '?v=' . time()];
        };
        $this->getDrawing();
        $editor = $this->getPoster($this->poster_arr);
        return ['pic_url' => $editor->qrcode_url];
    }


    protected function getDrawing()
    {
        array_push($this->poster_arr
            , $this->setImage(self::head($this), 97, 97, 80, 164)
            , $this->setImage($this->takeQrcode($this), 240, 240, 70, 946)
        );
        return $this;
    }
}