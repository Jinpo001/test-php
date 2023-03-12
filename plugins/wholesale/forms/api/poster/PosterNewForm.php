<?php

namespace app\plugins\wholesale\forms\api\poster;

use app\core\response\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\api\poster\common\StyleGrafika;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\Model;
use app\plugins\wholesale\forms\common\CommonForm;
use app\plugins\wholesale\models\WholesaleGoods;
use yii\helpers\ArrayHelper;

class PosterNewForm extends Model implements BasePoster
{
    public $style;
    public $typesetting;
    public $type;
    public $goods_id;
    public $color;

    public function rules()
    {
        return [
            [['style', 'typesetting', 'goods_id'], 'required'],
            [['style', 'typesetting', 'type', 'goods_id'], 'integer'],
            [['color'], 'string'],
        ];
    }

    public function poster()
    {
        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $this->get()
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ];
        }
    }

    public function get()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $class = $this->getClass($this->style);
        $form = new CommonGoodsDetail();
        $form->mall = \Yii::$app->mall;
        $goods = $form->getGoods($this->goods_id);

        if (!$goods) {
            throw new \Exception('海报商品不存在');
        }
        $prices = array_column($goods->attr, 'price');
        if (empty($prices)) {
            throw new \Exception('海报-规格数据异常');
        }

        $picUrl = \yii\helpers\BaseJson::decode($goods->picUrl);
        $pic_list = array_column($picUrl, 'pic_url');
        if (empty($pic_list)) {
            throw new \Exception('图片不能为空');
        }
        while (count($pic_list) < 5) {
            $pic_list = array_merge($pic_list, $pic_list);
        }
        $form->goods = $goods;
        $goods = $form->getAll();

        $wholesaleGoodsTemp = WholesaleGoods::findOne(['goods_id' => $this->goods_id]);
        $wholesaleGoods = ArrayHelper::toArray($wholesaleGoodsTemp);
        $wholesaleGoods['wholesale_rules'] = ($wholesaleGoods['wholesale_rules'] == '[]') ? [] : $wholesaleGoods['wholesale_rules'];
        $goods['wholesaleGoods'] = $wholesaleGoods;
        $minPrice = $goods['price_min'];
        $maxPrice = $goods['price_max'];
        CommonForm::getWholesalePrice($goods);
        if ($goods['price_section']) {
            $minPrice = $goods['price_section']['min_price'];
            $maxPrice = $goods['price_section']['max_price'];
        }

        $class->typesetting = $this->typesetting;
        $class->type = $this->type;
        $class->color = $this->color;
        $class->goods = $wholesaleGoodsTemp->goods;

        $class->other = [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ];
        $class->extraModel = PosterCustomize::className();
        return $class->build();
    }


    /**
     * @param int $key
     * @return StyleGrafika
     * @throws \Exception
     */
    private function getClass(int $key): StyleGrafika
    {
        $map = [
            1 => 'app\forms\api\poster\style\StyleOne',
            2 => 'app\forms\api\poster\style\StyleTwo',
            3 => 'app\forms\api\poster\style\StyleThree',
            4 => 'app\forms\api\poster\style\StyleFour',
        ];
        if (isset($map[$key]) && class_exists($map[$key])) {
            return new $map[$key];
        }
        throw new \Exception('调用错误');
    }
}
