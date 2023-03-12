<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/17
 * Time: 11:05 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\plugins\minishop\models\MinishopGoods;
use yii\helpers\Json;

class UpdateForm extends Model
{
    public $id;
    public $attr;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['attr'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $miniGoods = MinishopGoods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ]);
            if (!$miniGoods) {
                throw new \Exception('商品不存在或已被删除');
            }
            $data = [];
            $skus = [];
            $goodsInfo = Json::decode($miniGoods->goods_info, true);
            $stock = 0;
            $price = null;
            foreach ($this->attr as $attr) {
                if ($price === null) {
                    $price = $attr['price'];
                } else {
                    $price = min($attr['price'], $price);
                }
                $item = [
                    'out_sku_id' => $attr['out_sku_id'],
                    'sale_price' => $attr['price'] * 100,
                    'stock_num' => $attr['stock'],
                    'sku_code' => $attr['no'],
                    'barcode' => $attr['bar_code'],
                ];
                $skus[] = $item;
                $skusAttr = [];
                foreach ($attr['attr_list'] as $value) {
                    $skusAttr[] = [
                        'attr_key' => $value['attr_group_name'],
                        'attr_value' => $value['attr_name'],
                    ];
                }
                $item = array_merge($item, [
                    'out_product_id' => $miniGoods->goods_id,
                    'thumb_img' => $attr['thumb_img'],
                    'sku_attrs' => $skusAttr,
                    'weight' => $attr['weight'],
                    'market_price' => $attr['market_price'] * 100
                ]);
                $data[] = $item;
                $stock += $attr['stock'];
            }
            $goodsInfo['skus'] = $data;
            $res = $this->shopService->goods->updateWithoutAudit([
                'out_product_id' => $miniGoods->goods_id,
                'product_id' => $miniGoods->product_id,
                'path' => $goodsInfo['path'],
                'skus' => $skus,
            ]);
            $miniGoods->product_info = Json::encode($res['data'], JSON_UNESCAPED_UNICODE);
            $miniGoods->goods_info = Json::encode($goodsInfo, JSON_UNESCAPED_UNICODE);
            $miniGoods->stock = $stock;
            $miniGoods->price = $price;
            if (!$miniGoods->save()) {
                return $this->fail([
                    'msg' => $this->getErrorMsg($miniGoods)
                ]);
            }
            return $this->success([
                'msg' => '提交成功'
            ]);
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }
}

