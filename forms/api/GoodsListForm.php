<?php

namespace app\forms\api;

use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCoupon;
use app\forms\common\goods\CommonGoodsList;
use app\models\Mall;
use app\models\Model;

class GoodsListForm extends Model
{
    public $cat_id;
    public $sort;
    public $sort_type;
    public $keyword;
    public $page;
    public $mch_id;
    public $coupon_id;
    public $is_search;

    public function rules()
    {
        return [
            [['page'], 'default', 'value' => 1],
            [['is_search'], 'default', 'value' => 0],
            [['mch_id'], 'integer'],
        ];
    }

    public function search()
    {
        try {
            $form = new CommonGoodsList();
            if ($this->coupon_id && is_numeric($this->coupon_id)) {
                $commonCoupon = new CommonCoupon([
                    'mall' => \Yii::$app->mall,
                ], false);
                $commonCoupon->coupon_id = $this->coupon_id;
                $coupon = $commonCoupon->getDetail();
                if ($coupon->appoint_type == 2) {
                    $goodsWarehouseList = $coupon->goods;
                    $goodsWarehouseId = [];
                    foreach ($goodsWarehouseList as $goodsWarehouse) {
                        $goodsWarehouseId[] = $goodsWarehouse->id;
                    }
                    $form->goodsWarehouseId = $goodsWarehouseId;
                } elseif ($coupon->appoint_type == 1) {
                    $catList = $coupon->cat;
                    $this->cat_id = [];
                    foreach ($catList as $cats) {
                        $this->cat_id[] = $cats->id;
                    }
                }
                $form->cat_id = $this->cat_id;
            } else {
                $form->cat_id = is_numeric($this->cat_id) ? $this->cat_id : 0;
            }
            $form->sort = $this->sort;
            $form->status = 1;
            $form->sort_type = $this->sort_type;
            $form->keyword = $this->keyword;
            $form->page = $this->page;
            $form->mch_id = $this->mch_id ?: 0;
            $form->is_array = true;
            $form->mch_id && $this->sign = 'mch';
            $form->sign = $this->sign ? $this->sign === 'mall' ? [''] : $this->sign : ['mch', ''];
            if($this->is_search == 1 && is_array($form->sign)) {
                array_push($form->sign, 'pintuan','wholesale', 'flash_sale', 'exchange', 'booking', 'advance');
            }
            $form->isSignCondition = true;
            $form->is_sales = (new Mall())->getMallSettingOne('is_sales');
            $form->relations = ['goodsWarehouse', 'mallGoods', 'attr'];
            $form->deleteAttr = true;
            $form->is_show = 1;
            $list = $form->getList();
            $pintuanGoodsId = '';
            foreach ($list as $item) {
                if($item['sign'] == 'pintuan') {
                    if($pintuanGoodsId == $item['goods_warehouse_id']) {
                        $re = array_search($item, $list);
                        unset($list[$re]);
                    }else {
                        $pintuanGoodsId = $item['goods_warehouse_id'];
                    }
                }
            };
            $pintuanGoodsId = '';
            foreach ($list as $item) {
                if($item['sign'] == 'advance') {
                    if($pintuanGoodsId == $item['goods_warehouse_id']) {
                        $re = array_search($item, $list);
                        unset($list[$re]);
                    }else {
                        $pintuanGoodsId = $item['goods_warehouse_id'];
                    }
                }
            };
            $list = array_values($list);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $form->pagination,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}
