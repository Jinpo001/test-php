<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/12
 * Time: 11:11 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\plugins\minishop\models\MinishopGoods;
use app\plugins\wxapp\Plugin;
use yii\helpers\Json;

class OperateForm extends Model
{
    public $operate;
    public $id;

    /**
     * @var Plugin $plugin
     */
    protected $plugin;

    public function rules()
    {
        return [
            [['operate'], 'in', 'range' => ['delete', 'up', 'down', 'attr']],
            [['id'], 'integer']
        ];
    }

    public function execute()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $this->plugin = \Yii::$app->plugin->getPlugin('wxapp');
            switch ($this->operate) {
                case 'delete':
                    $this->delete();
                    break;
                case 'up':
                    $this->up();
                    break;
                case 'down':
                    $this->down();
                    break;
                case 'attr':
                    return $this->success($this->attr());
                    break;
                default:
            }
            return $this->success([
                'msg' => '操作成功'
            ]);
        } catch (\Exception $exception) {
            return $this->failByException($exception);
        }
    }

    protected function delete()
    {
        $miniGoods = MinishopGoods::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0
        ]);
        if (!$miniGoods) {
            throw new \Exception('商品不存在或已被删除');
        }
        $miniGoods->is_delete = 1;
        if (!$miniGoods->save()) {
            throw new \Exception($this->getErrorMsg($miniGoods));
        }
        $res = $this->plugin->getShopService()->goods->del([
            'product_id' => $miniGoods->product_id,
            'out_product_id' => $miniGoods->goods_id
        ]);
        return true;
    }

    protected function up()
    {
        $miniGoods = MinishopGoods::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0
        ]);
        if (!$miniGoods) {
            throw new \Exception('商品不存在或已被删除');
        }
        $miniGoods->status = 1;
        if (!$miniGoods->save()) {
            throw new \Exception($this->getErrorMsg($miniGoods));
        }
        $res = $this->plugin->getShopService()->goods->listing([
            'product_id' => $miniGoods->product_id,
            'out_product_id' => $miniGoods->goods_id
        ]);
        return true;
    }

    protected function down()
    {
        $miniGoods = MinishopGoods::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0
        ]);
        if (!$miniGoods) {
            throw new \Exception('商品不存在或已被删除');
        }
        $miniGoods->status = 0;
        if (!$miniGoods->save()) {
            throw new \Exception($this->getErrorMsg($miniGoods));
        }
        $res = $this->plugin->getShopService()->goods->delisting([
            'product_id' => $miniGoods->product_id,
            'out_product_id' => $miniGoods->goods_id
        ]);
        return true;
    }

    protected function attr()
    {
        $miniGoods = MinishopGoods::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0
        ]);
        if (!$miniGoods) {
            throw new \Exception('商品不存在或已被删除');
        }
        $goodsInfo = Json::decode($miniGoods->goods_info, true);
        $attr = [];
        foreach ($goodsInfo['skus'] as $sku) {
            $attrList = [];
            foreach ($sku['sku_attrs'] as $item) {
                $attrList[] = [
                    'attr_name' => $item['attr_value'],
                    'attr_group_name' => $item['attr_key'],
                ];
            }
            $attr[] = [
                'attr_list' => $attrList,
                'no' => $sku['sku_code'],
                'price' => price_format($sku['sale_price'] / 100),
                'stock' => $sku['stock_num'],
                'bar_code' => $sku['barcode'],
                'weight' => $sku['weight'],
                'out_sku_id' => $sku['out_sku_id'],
                'thumb_img' => $sku['thumb_img'],
                'market_price' => price_format($sku['market_price'] / 100)
            ];
        }
        return [
            'attr' => $attr,
            'attr_groups' => $goodsInfo['attr_group']
        ];
    }
}
