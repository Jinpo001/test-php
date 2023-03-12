<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/9
 * Time: 5:18 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\plugins\minishop\models\MinishopGoods;
use yii\helpers\Json;

class IndexForm extends Model
{
    public $page;
    public $sort_prop;
    public $sort_type;
    public $keyword;

    public function rules()
    {
        return [
            [['page', 'sort_type'], 'integer'],
            ['page', 'default', 'value' => 1],
            [['sort_prop', 'keyword'], 'trim'],
            [['sort_prop', 'keyword'], 'string'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $res = $this->check();
        if ($res !== true) {
            return $res;
        }

        $sort = ['id' => SORT_DESC];
        if ($this->sort_prop) {
            switch ($this->sort_prop) {
                case 'id':
                    $sort['id'] = $this->sort_type == 0 ? SORT_DESC : SORT_ASC;
                    break;
                case 'price':
                    $sort['price'] = $this->sort_type == 0 ? SORT_DESC : SORT_ASC;
                    break;
                case 'stock':
                    $sort['stock'] = $this->sort_type == 0 ? SORT_DESC : SORT_ASC;
                    break;
                default:
            }
        }

        /* @var MinishopGoods[] $list */
        $list = MinishopGoods::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0,
        ])->keyword($this->keyword, [
            'or',
            ['like', 'title', $this->keyword],
            ['goods_id' => $this->keyword]
        ])->orderBy($sort)->page($pagination)->all();
        $newList = [];
        foreach ($list as $item) {
            $goodsInfo = Json::decode($item->goods_info, true);
            $newList[] = [
                'id' => $item->id,
                'goods_id' => $item->goods_id,
                'title' => $item->title,
                'cat' => $item->third_cat,
                'brand' => $item->brand,
                'cover_pic' => $goodsInfo['head_img'][0],
                'price' => $item->price,
                'stock' => $item->stock,
                'created_at' => $item->created_at,
                'apply_status' => $item->apply_status,
                'status' => $item->status,
                'third_cat_id' => $goodsInfo['third_cat_id']
            ];
        }
        return $this->success([
            'can_use' => true,
            'list' => $newList,
            'pagination' => $pagination
        ]);
    }

    protected function check()
    {
        if (\Yii::$app->cache->get('minishop_status_' . \Yii::$app->mall->id)) {
            return true;
        }
        try {
            $res = $this->shopService->register->check();
            switch ($res['data']['status']) {
                case 0:
                    return $this->success([
                        'can_use' => false,
                        'content' => '该小程序自定义版交易组件接入审核中，请开通后再使用'
                    ]);
                    break;
                case 1:
                case 2:
                    \Yii::$app->cache->set('minishop_status_' . \Yii::$app->mall->id, true, 86400);
                    return true;
                    break;
                case 3:
                    return $this->success([
                        'can_use' => false,
                        'content' => '该小程序自定义版交易组件已被封禁，请开通后再使用'
                    ]);
                    break;
                case 4:
                    return $this->success([
                        'can_use' => false,
                        'content' => '该小程序自定义版交易组件审批不通过，请开通后再使用'
                    ]);
                    break;
                default:
                    return $this->success([
                        'can_use' => false,
                        'content' => '该小程序未开通自定义版交易组件，请开通后再使用'
                    ]);
            }
        } catch (\Exception $exception) {
            return $this->success([
                'can_use' => false,
                'content' => '该小程序还未开通自定义版交易组件，请开通后再使用'
            ]);
        }
    }

    public function getCat()
    {
        if ($cat = \Yii::$app->cache->get('wxapp_shop_cat_list')) {
            return $this->success([
                'list' => Json::decode($cat, true)
            ]);
        }

        try {
            $res = $this->shopService->register->getCat();
            $newList = [];
            foreach ($res['third_cat_list'] as $value) {
                // 需要资质的分类去掉不显示、一级分类名称为空的不显示
                if ($value['qualification_type'] != 0 || $value['product_qualification_type'] != 0 || $value['first_cat_name'] == '') {
                    continue;
                }
                if (!isset($newList[$value['first_cat_id']])) {
                    $newList[$value['first_cat_id']] = [
                        'label' => $value['first_cat_name'],
                        'value' => $value['first_cat_id'],
                        'children' => []
                    ];
                }
                if (!isset($newList[$value['first_cat_id']]['children'][$value['second_cat_id']])) {
                    $newList[$value['first_cat_id']]['children'][$value['second_cat_id']] = [
                        'label' => $value['second_cat_name'],
                        'value' => $value['second_cat_id'],
                        'children' => []
                    ];
                }
                $newList[$value['first_cat_id']]['children'][$value['second_cat_id']]['children'][] = [
                    'label' => $value['third_cat_name'],
                    'value' => $value['third_cat_id'],
                ];
            }
            foreach ($newList as &$item) {
                $item['children'] = array_values($item['children']);
            }
            unset($item);
            $newList = array_values($newList);
            \Yii::$app->cache->set('wxapp_shop_cat_list', Json::encode($newList, JSON_UNESCAPED_UNICODE), 86400);
            return $this->success([
                'list' => $newList
            ]);
        } catch (\Exception $exception) {
            return $this->fail([
                'msg' => $exception->getMessage()
            ]);
        }
    }
}
