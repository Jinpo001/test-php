<?php

namespace app\plugins\wholesale\forms\api\poster;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\poster\PosterConfigTrait;
use app\models\Model;
use app\plugins\wholesale\forms\common\CommonForm;
use app\plugins\wholesale\models\WholesaleGoods;
use app\plugins\wholesale\Plugin;
use yii\helpers\ArrayHelper;

class PosterConfigForm extends Model
{
    use PosterConfigTrait;

    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'config' => $this->getConfig(),
                    'info' => $this->getAll(),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getGoods(): array
    {
        $form = new CommonGoodsDetail();
        $form->mall = \Yii::$app->mall;
        $goods = $form->getGoods($this->goods_id);
        $goodsName = $goods->name;

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

        $wholesaleGoods = ArrayHelper::toArray(WholesaleGoods::findOne(['goods_id' => $this->goods_id]));
        $wholesaleGoods['wholesale_rules'] = ($wholesaleGoods['wholesale_rules'] == '[]') ? [] : $wholesaleGoods['wholesale_rules'];
        $goods['wholesaleGoods'] = $wholesaleGoods;
        $minPrice = $goods['price_min'];
        $maxPrice = $goods['price_max'];
        CommonForm::getWholesalePrice($goods);
        if ($goods['price_section']) {
            $minPrice = $goods['price_section']['min_price'];
            $maxPrice = $goods['price_section']['max_price'];
        }
        return [
            'goods_name' => $goodsName,
            'is_negotiable' => $goods->mallGoods->is_negotiable ?? 0,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'multi_map' => $pic_list,
        ];
    }

    public function getExtra(): array
    {
        $model = new PosterCustomize();
        $data = $model->traitMultiMapContent();

        $extra_multiMap = $this->formatType($data);
        return [
            'extra_multiMap' => $extra_multiMap,
        ];
    }

    public function getPlugin(): array
    {
        return [
            'sign' => (new Plugin())->getName(),
        ];
    }
}
