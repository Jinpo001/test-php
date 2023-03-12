<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\integral_mall\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\integral_mall\models\IntegralMallGoods;
use app\plugins\integral_mall\models\IntegralMallGoodsAttr;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\integral_mall\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('integralMallGoods');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $goodsPrice = 0;
        $attrId = 0;
        foreach ($goods->attr as $aItem) {
            if ($goodsPrice == 0) {
                $goodsPrice = $aItem->price;
                $attrId = $aItem->id;
            } else {
                if ($aItem->price < $goodsPrice) {
                    $goodsPrice = $aItem->price;
                    $attrId = $aItem->id;
                }
            }
        }

        $goodsAttr = IntegralMallGoodsAttr::findOne(['goods_attr_id' => $attrId, 'is_delete' => 0]);
        $newItem['integral_num'] = $goodsAttr ? $goodsAttr->integral_num : 0;

        $newItem['integralMallGoods'] = isset($goods->integralMallGoods) ? ArrayHelper::toArray($goods->integralMallGoods) : [];

        return $newItem;
    }

    public function setGoodsSort($query, $search)
    {
        if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
            $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
            if ($search['sort_prop'] == 'integralMallGoods.integral_num') {
                $query->leftJoin(['img' => IntegralMallGoods::tableName()], 'img.goods_id=g.id');
                $query->orderBy(['img.integral_num' => $sortType]);
            } else {
                $query->orderBy(['g.' . $search['sort_prop'] => $sortType]);
            }
        } else {
            $query->orderBy(['g.created_at' => SORT_DESC]);
        }

        return $query;
    }
}