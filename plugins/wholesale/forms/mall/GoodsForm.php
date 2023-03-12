<?php

namespace app\plugins\wholesale\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;
use app\plugins\wholesale\models\Goods;
use app\plugins\wholesale\models\WholesaleGoods;

/**
 * @property Mall $mall
 */
class GoodsForm extends Model
{
    public $mall;
    public $id;
    public $search;
    public $sort;
    public $batch_ids;
    public $status;
    public $page;

    public function rules()
    {
        return [
            [['id', 'sort', 'status', 'page'], 'integer'],
            [['search', 'batch_ids'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序',
            'id' => '商品ID'
        ];
    }

    public function getList()
    {
        $search = \Yii::$app->serializer->decode($this->search);
        $form = new CommonGoodsList();
        $form->cat_id = $search['cats'];
        $form->keyword = $search['keyword'];
        $form->date_start = $search['date_start'] ?? null;
        $form->date_end = $search['date_end'] ?? null;
        $form->model = 'app\plugins\wholesale\models\Goods';
        $form->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
        $form->relations = ['goodsWarehouse.cats', 'attr'];
        $form->is_array = 1;

        if (array_key_exists('sort_prop', $search) && $search['sort_prop']) {
            $form->sort = 6;
            $form->sort_prop = $search['sort_prop'];
            $form->sort_type = $search['sort_type'];
        } else {
            $form->sort = 2;
        }

        switch ($search['status']) {
            case 0:
                $form->status = 0;
                break;
            case 1:
                $form->status = 1;
                break;
            case 2:
                $form->is_sold_out = 1;
                break;
        }

        $form->page = $this->page;
        $list = $form->search();

        foreach ($list as &$item) {
            $item['status'] = (int)$item['status'];
            $item['cats'] = $item['goodsWarehouse']['cats'];

            $goodsStock = 0;
            foreach ($item['attr'] as $aItem) {
                $goodsStock += $aItem['stock'];
            }
            $item['goods_stock'] = $goodsStock;
        }
        unset($item);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function getDetail()
    {
        $form = new CommonGoods();
        $res = $form->getGoodsDetail($this->id);
        $wholesaleGoods = WholesaleGoods::findOne(['goods_id' => $this->id, 'is_delete' => 0]);
        $res['wholesale_type'] = $wholesaleGoods->type;
        $res['wholesale_rules'] = json_decode($wholesaleGoods->wholesale_rules, true);
        $res['rise_num'] = $wholesaleGoods->rise_num;
        $res['rules_status'] = $wholesaleGoods->rules_status;
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $res,
            ]
        ];
    }

    public function switchStatus()
    {
        try {
            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->status = $goods->status ? 0 : 1;

            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
