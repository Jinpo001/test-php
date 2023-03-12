<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/11/5
 * Time: 16:59
 */

namespace app\plugins\wholesale\forms\api;

use app\core\response\ApiCode;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Model;
use app\plugins\wholesale\models\Goods;
use app\plugins\wholesale\Plugin;

class CatsForm extends Model
{
    public function getList()
    {
        try {
            $goodsWarehouseIds = Goods::find()->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'sign' => (new Plugin())->getName(),
                    'status' => 1,
                ]
            )->select('goods_warehouse_id');
            $catIds = GoodsCatRelation::find()->where(
                [
                    'is_delete' => 0,
                    'goods_warehouse_id' => $goodsWarehouseIds,
                ]
            )->select('cat_id');

            $catList = GoodsCats::find()->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => 0,
                    'is_delete' => 0,
                    'status' => 1,
                ]
            )
            ->andWhere(
                [
                    'or',
                    ['id' => $catIds],
                    ['parent_id' => $catIds]
                ]
            )
            ->orderBy(['sort' => SORT_ASC])->all();

            $parentIds = [];
            foreach ($catList as $item) {
                if ($item->parent_id) {
                    $parentIds[] = $item->parent_id;
                } else {
                    $parentIds[] = $item->id;
                }
            }

            $catList = GoodsCats::find()->where(
                [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => 0,
                    'is_delete' => 0,
                    'status' => 1,
                ]
            )
            ->andWhere(
                [
                    'or',
                    ['id' => $parentIds],
                    ['parent_id' => $parentIds]
                ]
            )
            ->orderBy(['sort' => SORT_ASC])->all();


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

            array_unshift($newCatList, ['id' => 0, 'name' => '全部']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => "请求成功",
                'data' => [
                    'list' => $newCatList
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }
}
