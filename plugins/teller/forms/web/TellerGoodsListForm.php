<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\models\Model;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\models\TellerCashier;

class TellerGoodsListForm extends Model
{
    public $keyword;
    public $cat_id;

    public function rules()
    {
        return [
            [['keyword'], 'string'],
            [['keyword'], 'trim'],
            [['cat_id'], 'integer'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {

            $keyword = $this->keyword;
            $catId = $this->cat_id;

            $query = Goods::find()->alias('g')->andWhere([
                'g.mall_id' => \Yii::$app->mall->id,
                'g.mch_id' => 0,
                'g.is_delete' => 0,
                'g.status' => 1,
                'g.sign' => '',
            ])
                ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'g.goods_warehouse_id = gw.id')
                ->andWhere(['gw.type' => 'goods', 'gw.mall_id' => \Yii::$app->mall->id, 'gw.is_delete' => 0])
                ->leftJoin(['mg' => MallGoods::tableName()], 'g.id = mg.goods_id')
                ->andWhere(['mg.is_negotiable' => 0]);

            if ($keyword) {
                $goodsIds = GoodsAttr::find()->andWhere(['bar_code' => $keyword, 'is_delete' => 0])->select('goods_id');
                $query->andWhere([
                    'or',
                    ['like', 'gw.name', $keyword],
                    ['g.id' => $goodsIds]
                ]);
            }

            if ($catId) {
                $cat = GoodsCats::find()->select('id')->andWhere([
                    'is_delete' => 0,
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'status' => 1,
                ])
                    ->andWhere([
                        'OR',
                        ['parent_id' => GoodsCats::find()->andWhere([
                            'parent_id' => $catId,
                        ])->select('id')],
                        ['parent_id' => $catId],
                        ['id' => $catId],
                    ])->select('id');

                $goodsWarehouseId = GoodsCatRelation::find()->andWhere([
                    'is_delete' => 0
                ])
                    ->andWhere(['in', 'cat_id', $cat])
                    ->select('goods_warehouse_id');

                $query->andWhere(['gw.id' => $goodsWarehouseId]);
            }

            $list = $query
                ->with('attr', 'goodsWarehouse', 'mallGoods')
                ->page($pagination)
                ->all();

            $list = array_map(function($item) {

                $goodsStock = 0;
                $minPprice = 0;
                foreach ($item->attr as $attr) {
                    $goodsStock += $attr->stock;
                    $minPprice = $minPprice == 0 ? $attr->price : min($minPprice, $attr->price)
;                }

                return [
                    'id' => $item->id,
                    'name' => $item->goodsWarehouse->name,
                    'cover_pic' => $item->goodsWarehouse->cover_pic,
                    'stock' => $goodsStock,
                    'price' => $minPprice
                ];
            }, $list);


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list'=> $list,
                    'pagination' => $pagination
                ],
            ];
        }catch(\Exception $exception) {
            \Yii::error($exception);
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
