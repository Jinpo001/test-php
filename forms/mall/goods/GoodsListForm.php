<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\goods;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\mall\export\CommonExport;
use app\forms\mall\export\MallGoodsExport;
use app\forms\mall\export\jobs\ExportJob;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsCats;

class GoodsListForm extends BaseGoodsList
{
    public $choose_list;
    public $flag;

    public $is_show_attr;
    public $is_time;
    public $is_status;

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['is_show_attr', 'is_time', 'is_status'], 'integer'],
        ]);
    }

    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign' => \Yii::$app->user->identity->mch_id > 0 ? 'mch' : '',
            'g.mch_id' => \Yii::$app->user->identity->mch_id,
        ])->keyword($this->is_status, ['g.status' => 1])->keyword(
            $this->is_time,
            [
                'OR',
                ['g.is_time' => 0],
                [
                    'AND',
                    ['g.is_time' => 1],
                    ['<=', 'g.sell_begin_time', date('Y-m-d H:i:s')],
                    ['>', 'g.sell_end_time', date('Y-m-d H:i:s')],
                ]
            ]
        )->with('mallGoods');
        if (\Yii::$app->user->identity->mch_id > 0) {
            $query->with('mchGoods', 'goodsWarehouse.mchCats');
        }

        if ($this->flag == "EXPORT") {
            if ($this->choose_list && count($this->choose_list) > 0) {
                $query->andWhere(['g.id' => $this->choose_list]);
            }

            $queueId = CommonExport::handle([
                'export_class' => 'app\\forms\\mall\\export\\MallGoodsExport',
                'params' => [
                    'query' => $query,
                ]
            ]);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'queue_id' => $queueId
                ]
            ];
        }

        return $query;
    }

    public function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['mallGoods'] = [];
        $newItem['mallGoods']['id'] = $goods->mallGoods->id;
        $newItem['mallGoods']['is_quick_shop'] = $goods->mallGoods->is_quick_shop;
        $newItem['mallGoods']['is_sell_well'] = $goods->mallGoods->is_sell_well;
        $newItem['mallGoods']['is_negotiable'] = $goods->mallGoods->is_negotiable;
        $newItem = $this->mchGoodsData($goods, $newItem);

        //todo 兑换中心使用规格 可能有重复查询
        if ($this->is_show_attr == 1) {
            $common = CommonGoods::getCommon();
            $detail = $common->getGoodsDetail($goods->id, false);
            $newItem['attr'] = $detail['attr'];
            $newItem['attr_groups'] = $detail['attr_groups'];
        }
        return $newItem;
    }

    private function mchGoodsData($goods, $newItem)
    {

        $newItem['mchCats'] = [];
        if ($goods->goodsWarehouse && $goods->goodsWarehouse->mchCats) {
            $newCats = [];
            /** @var GoodsCats $cat */
            foreach ($goods->goodsWarehouse->mchCats as $cat) {
                $newCatItem = [];
                $newCatItem['id'] = $cat->id;
                $newCatItem['name'] = $cat->name;
                $newCats[] = $newCatItem;
            }
            $newItem['mchCats'] = $newCats;
        }

        $newItem['mchGoods'] = [];
        if (\Yii::$app->user->identity->mch_id > 0 && $goods->mchGoods) {
            $newItem['mchGoods']['id'] = $goods->mchGoods->id;
            $newItem['mchGoods']['sort'] = $goods->mchGoods->sort;
            $newItem['mchGoods']['status'] = $goods->mchGoods->status;
            $newItem['mchGoods']['remark'] = $goods->mchGoods->remark;
        }

        return $newItem;
    }
}
