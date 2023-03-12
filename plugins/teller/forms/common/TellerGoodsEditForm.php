<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\common;


use app\forms\mall\goods\BaseGoodsEdit;
use app\helpers\PluginHelper;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\plugins\teller\Plugin;

class TellerGoodsEditForm extends BaseGoodsEdit
{
    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    public function save()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $pluginName = $this->setGoodsSign();

            $goods = Goods::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'sign' => $pluginName,
                'is_delete' => 0
            ])->with('attr')->one();

            if (!$goods) {
                $goodsPic = PluginHelper::getPluginBaseAssetsUrl($pluginName) . '/img/goods-pic.png';

                $goodsWarehouse = new GoodsWarehouse();
                $goodsWarehouse->mall_id = \Yii::$app->mall->id;
                $goodsWarehouse->name = '临时加钱';
                $goodsWarehouse->detail = '临时加钱';
                $goodsWarehouse->cover_pic = $goodsPic;
                $goodsWarehouse->pic_url = json_encode([
                    [
                        'id' => 0,
                        'pic_url' => $goodsPic
                    ]
                ]);
                $res = $goodsWarehouse->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsWarehouse));
                }

                $attr = $this->defaultAttr($goodsPic);
                $goods = new Goods();
                $goods->mall_id = \Yii::$app->mall->id;
                $goods->goods_warehouse_id = $goodsWarehouse->id;
                $goods->attr_groups = \Yii::$app->serializer->encode($attr['attr_groups']);
                $goods->freight_id = 0;
                $goods->individual_share = 1;
                $goods->use_attr = 0;
                $goods->status = 1;
                $goods->sign = $pluginName;

                $res = $goods->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goods));
                }

                $goodsAttr = new GoodsAttr();
                $goodsAttr->goods_id = $goods->id;
                $goodsAttr->sign_id = $attr['sign_id'];
                $res = $goodsAttr->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($goodsAttr));
                }
            }

            foreach ($goods->attr as $attr) {
                if ($attr->stock <= 0) {
                    $attr->stock = 999999;
                    $attr->save();
                }
            }

            $transaction->commit();
            return $goods;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error('收银台商品异常----->' . $e->getMessage());
            throw $e;
        }
    }

    private function defaultAttr($goodsPic)
    {
        $attrList = [
            [

                'attr_group_name' => '规格',
                'attr_group_id' => 1,
                'attr_id' => 1,
                'attr_name' => '默认',
            ]
        ];

        $count = 1;
        $attrGroups = [];
        foreach ($attrList as &$item) {
            $item['attr_group_id'] = $count;
            $count++;
            $item['attr_id'] = $count;
            $count++;
            $newItem = [
                'attr_group_id' => $item['attr_group_id'],
                'attr_group_name' => $item['attr_group_name'],
                'attr_list' => [
                    [
                        'attr_id' => $item['attr_id'],
                        'attr_name' => $item['attr_name']
                    ]
                ]
            ];
            $attrGroups[] = $newItem;
        }
        unset($item);

        // 未使用规格 就添加一条默认规格
        $newAttrs = [
            [
                'attr_list' => $attrList,
                'stock' => 0,
                'price' => 0,
                'no' => '',
                'weight' => 0,
                'pic_url' => $goodsPic,
            ]
        ];

        $signIds = '';
        foreach ($attrList as $aLItem) {
            $signIds .= $signIds ? ':' . (int)$aLItem['attr_id'] : (int)$aLItem['attr_id'];
        }

        return [
            'attr_groups' => $attrGroups,
            'attrs' => $newAttrs,
            'sign_id' => $signIds,
        ];
    }
}