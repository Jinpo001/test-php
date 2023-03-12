<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\wholesale\models;


use app\models\GoodsCatRelation;

class Goods extends \app\models\Goods
{
    public function getCat()
    {
        return $this->hasOne(GoodsCatRelation::className(), ['goods_warehouse_id' => 'goods_warehouse_id']);
    }

    public function getWholesaleGoods()
    {
        return $this->hasOne(WholesaleGoods::className(), ['goods_id' => 'id']);
    }
}
