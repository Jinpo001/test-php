<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\step\forms\mall;


use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\step\models\StepGoods;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList
{
    public $goodsModel = 'app\plugins\step\models\Goods';

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->plugin->getCurrentPlugin()->getName(),
        ])->with('stepGoods', 'attr.stepGoods');

        return $query;
    }

    protected function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['stepGoods'] = isset($goods->stepGoods) ? ArrayHelper::toArray($goods->stepGoods) : [];
        $newItem['attr']['stepGoods'] = isset($goods->attr->stepGoods) ? ArrayHelper::toArray($goods->attr->stepGoods) : [];

        $num_count = 0;
        foreach($goods->attr as $key => $item) {
            $newItem['attr'][$key]['step_currency'] = $item->stepGoods->currency;
            $num_count += $item->stock;
        }
        $newItem['num_count'] = $num_count;

        return $newItem;
    }

    public function setGoodsSort($query, $search)
    {
        if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
            $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
            if ($search['sort_prop'] == 'stepGoods.currency') {
                $query->leftJoin(['sg' => StepGoods::tableName()], 'sg.goods_id=g.id');
                $query->orderBy(['sg.currency' => $sortType]);
            } else {
                $query->orderBy(['g.' . $search['sort_prop'] => $sortType]);
            }
        } else {
            $query->orderBy(['g.created_at' => SORT_DESC]);
        }

        return $query;
    }
}