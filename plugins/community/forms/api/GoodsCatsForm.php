<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/13
 * Time: 11:36 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\community\forms\api;

use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\GoodsAuth;
use app\forms\common\goods\LimitBuy;
use app\helpers\ArrayHelper;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\forms\Model;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityGoodsAttr;
use app\plugins\community\models\CommunitySwitch;
use app\plugins\community\models\Goods;
use yii\db\Query;

class GoodsCatsForm extends Model
{
    public $id;
    public $middleman_id;
    public $type;
    public $cat_id;

    public function rules()
    {
        return [
            [['id', 'middleman_id', 'type', 'cat_id'], 'integer'],
            ['type', 'default', 'value' => 1]
        ];
    }

    public function getCats()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $activity_id = $this->id;
        $middleman_id = $this->middleman_id;
        $type = $this->type;
        $setting = CommonSetting::getCommon()->getSetting();
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\community\models\Goods';
        $form->sign = 'community';
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->status = 1;
        $form->is_show = 1;
        /** @var Query $query */
        $form->getQuery();
        $query = $form->query;

        if ($setting['sell_out_sort'] == 2) {
            $query->andWhere(['>', 'g.goods_stock', 0]);
            $query->orderBy('g.sort ASC');
        } elseif ($setting['sell_out_sort'] == 3) {
            $query->orderBy('`g`.`goods_stock` = 0,`g`.`sort`');
        } else {
            $query->orderBy('g.sort ASC');
        }

        $query->rightJoin(
            ['cg' => CommunityGoods::tableName()],
            'cg.goods_id = g.id and cg.is_delete = 0 and cg.activity_id = ' . $activity_id
        );

        if ($type == 1) {
            $query->andWhere(
                ['not in', 'cg.goods_id', CommunitySwitch::find()->select('goods_id')
                    ->where(['activity_id' => $activity_id, 'middleman_id' => $middleman_id, 'is_delete' => 0])]
            );
        }

        $goodsWarehouseIds = $query->select('goods_warehouse_id')->column();

        $catIds = GoodsCatRelation::find()->where([
            'is_delete' => 0,
            'goods_warehouse_id' => $goodsWarehouseIds,
        ])->select('cat_id');

        $catList = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => 0,
            'is_delete' => 0,
            'status' => 1,
            'id' => $catIds
        ])->with(['parent.parent'])->all();


        $parent = [];
        /** @var GoodsCats $cat */
        foreach ($catList as $cat) {
            $parent[] = $this->getDetail($cat);
        }

        $catList = GoodsCats::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => 0,
            'is_delete' => 0,
            'status' => 1,
            'id' => $parent
        ])->orderBy(['sort' => SORT_ASC])->all();

        $newCatList = [];
        /** @var GoodsCats $cat */
        foreach ($catList as $cat) {
            if ($cat->parent_id != 0) {
                continue;
            }
            $newCatItem = [];
            $newCatItem['id'] = $cat->id;
            $newCatItem['name'] = $cat->name;
            $newCatList[] = $newCatItem;
        }

        array_unshift($newCatList, ['id' => -1, 'name' => '全部']);

        return $this->success([
            'list' => $newCatList
        ]);
    }

    /**
     * @param GoodsCats $cat
     */
    protected function getDetail($cat)
    {
        if ($cat->parent_id != 0) {
            return $this->getDetail($cat->parent);
        } else {
            return $cat->id;
        }
    }

    public function goodsList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $activityId = $this->id;
        $middlemanId = $this->middleman_id;
        $type = $this->type;
        $setting = CommonSetting::getCommon()->getSetting();
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\community\models\Goods';
        $form->sign = 'community';
        $form->relations = ['goodsWarehouse', 'attr'];
        $form->status = 1;
        $form->is_show = 1;
        $form->cat_id = $this->cat_id;
        /** @var Query $query */
        $form->getQuery();
        $query = $form->query;

        if ($setting['sell_out_sort'] == 2) {
            $query->andWhere(['>', 'g.goods_stock', 0]);
            $query->orderBy('g.sort ASC');
        } elseif ($setting['sell_out_sort'] == 3) {
            $query->orderBy('`g`.`goods_stock` = 0,`g`.`sort`');
        } else {
            $query->orderBy('g.sort ASC');
        }

        $query->rightJoin(
            ['cg' => CommunityGoods::tableName()],
            'cg.goods_id = g.id and cg.is_delete = 0 and cg.activity_id = ' . $activityId
        );

        if ($type == 1) {
            $query->andWhere(
                ['not in', 'cg.goods_id', CommunitySwitch::find()->select('goods_id')
                    ->where(['activity_id' => $activityId, 'middleman_id' => $middlemanId, 'is_delete' => 0])]
            );
        }
        $list = $query->page($pagination)->asArray()->all();
        $goods = new Goods();
        foreach ($list as &$item) {
            $price = [];
            $profit_price = [];
            $item['attr_groups'] = \Yii::$app->serializer->decode($item['attr_groups']);
            $attrList = $goods->resetAttr($item['attr_groups']);
            foreach ($item['attr'] as &$a) {
                $a['attr_list'] = $attrList[$a['sign_id']];
                array_push($price, (float)$a['price']);
                $attr = CommunityGoodsAttr::findOne(['goods_id' => $item['id'], 'attr_id' => $a['id']]);
                if (empty($attr)) {
                    continue;
                }
                array_push($profit_price, bcsub($a['price'], $attr->supply_price));
                $a['stock'] = intval($a['stock']);
            }
            unset($a);
            if (empty($profit_price)) {
                $profit_price = [0];
            }

            $item['cover_pic'] = $item['goodsWarehouse']['cover_pic'];
            $item['type'] = $item['goodsWarehouse']['type'];
            $item['name'] = $item['goodsWarehouse']['name'];
            $item['original_price'] = $item['goodsWarehouse']['original_price'];


            $item['sales'] = '已售：' . ($item['sales'] + ($type == 1 ? $item['virtual_sales'] : 0)) . '件';
            $item['page_url'] = '/plugins/community/detail/detail?goods_id=' . $item['id'];
            $item['is_open'] = empty(CommunitySwitch::findOne([
                'activity_id' => $activityId, 'goods_id' => $item['id'],
                'middleman_id' => $middlemanId, 'is_delete' => 0
            ])) ? 1 : 0;
            $item['min_price'] = min($price) ?? 0;
            switch ($item['use_attr']) {
                case 0:
                    $item['profit_price'] = price_format(max($profit_price) ?? 0);
                    break;
                case 1:
                    $item['profit_price'] = [
                        'min_price' => price_format(min($profit_price) ?? 0),
                        'max_price' => price_format(max($profit_price) ?? 0),
                    ];
                    break;
            }

            //商品开关，0关，1开
            if ($type == 2) {
                $item['switch'] = CommunitySwitch::getSwitch($item['id'], $middlemanId) ? 0 : 1;
            }
            $item['goodsWarehouse'] = [
                'unit' => $item['goodsWarehouse']['unit']
            ];
            $item['goods_stock'] = intval($item['goods_stock']);
            $item['use_attr'] = intval($item['use_attr']);
            $item['buy_goods_auth'] = GoodsAuth::create($item['sign'])->checkBuyAuth((object)$item);
            $limitBuy = LimitBuy::create($item['sign']);
            $item['unit'] = $item['goodsWarehouse']['unit'];
            $limitBuy->goods = (object)$item;
            $item['limit_buy'] = $limitBuy->getLimitBuy();
            $item = ArrayHelper::filter($item, [
                'activity_id', 'attr', 'attr_groups', 'cover_pic', 'goodsWarehouse', 'goods_stock', 'id', 'min_price',
                'name', 'page_url', 'price', 'profit_price', 'sales', 'virtual_sales', 'use_attr', 'type', 'sign',
                'switch', 'buy_goods_auth', 'min_number', 'limit_buy'
            ]);
        }
        unset($item);
        return $this->success([
            'list' => $list,
            'pagination' => $pagination
        ]);
    }
}
