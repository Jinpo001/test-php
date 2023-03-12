<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\diy\forms\common;


use app\forms\api\goods\ApiGoods;
use app\models\Model;
use app\plugins\composition\forms\common\combination\FactoryCombination;
use app\plugins\composition\models\Composition;
use app\forms\common\goods\GoodsAuth;

class DiyCompositionForm extends Model
{
    use TraitGoods;

    public function getGoodsIds($data)
    {
        $ids = [];
        foreach ($data['list'] as $item) {
            $ids[] = $item['composition_id'];
        }
        return $ids;
    }

    public function getGoodsById($goodsIds)
    {
        if (!$goodsIds) {
            return [];
        }
        $query = Composition::find()->where([
            'id' => $goodsIds,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->with(['compositionGoods.goods.goodsWarehouse', 'compositionGoods.goods.attr'])
            ->page($pagination)
            ->orderBy(['sort' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        $newList = [];
        /** @var Composition $composition */
        foreach ($list as $composition) {
            $compositionClass = FactoryCombination::getCommon()->getCombination($composition->type);
            $compositionClass->composition = $composition;
            $goodsList = $compositionClass->getGoodsList($composition);
            $newList[] = [
                'composition_id' => $composition->id,
                'page_url' => '/plugins/composition/detail/detail?composition_id=' . $composition->id,
                'name' => $composition->name,
                'price' => $goodsList['min_composition_price'],
                'price_content' => $this->getPriceContent(0, $goodsList['min_composition_price']),
                'cover_pic_list' => array_merge(array_column($goodsList['host_list'], 'cover_pic'), array_column($goodsList['goods_list'], 'cover_pic')),
                'type' => $composition->type,
                'tag' => $composition->type != 1 ? $composition->type == 2 ? '搭配套餐' : '' : '固定套餐',
                'goods_list' => $goodsList,
                'buy_goods_auth' => true
            ];
        }
        return $newList;
    }

    public function getPriceContent($isNegotiable, $minPrice)
    {
        if ($isNegotiable == 1) {
            $priceContent = '价格面议';
        } elseif ($minPrice > 0) {
            $priceContent = '￥' . $minPrice;
        } else {
            $priceContent = '免费';
        }
        return $priceContent;
    }

    public function getNewGoods($data, $goods)
    {
        $newArr = [];
        foreach ($data['list'] as $item) {
            foreach ($goods as $gItem) {
                if ($item['composition_id'] == $gItem['composition_id']) {
                    $newArr[] = $gItem;
                    break;
                }
            }
        }
        $data['list'] = $newArr;
        return $data;
    }

}