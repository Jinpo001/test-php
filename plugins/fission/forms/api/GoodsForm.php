<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/26
 * Time: 5:10 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\api;

use app\forms\common\goods\CommonGoods;
use app\forms\common\goods\CommonGoodsDetail;
use app\models\CityDeliverySetting;
use app\models\Goods;
use app\models\Mall;
use app\models\User;
use app\plugins\fission\forms\Model;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @property Mall $mall
 * @property User $user
 */
class GoodsForm extends Model
{
    public $id;
    public $mall;
    public $user;

    public function rules()
    {
        return [
            [['id'], 'integer'],
        ];
    }

    /**
     * @param $goods
     * @return array|null
     */
    public function shareQuick($goods, $sales)
    {
        $plugin = 'quick_share';
        if (\Yii::$app->plugin->getInstalledPlugin($plugin)) {
            return \app\plugins\quick_share\forms\common\CommonQuickShare::getExtraInfo($goods, $sales);
        } else {
            return null;
        }
    }

    private function setLog(Goods $goods)
    {
        $goods->detail_count += 1;
        $goods->save();
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $form = new CommonGoodsDetail();
            $form->user = \Yii::$app->user->identity;
            $form->mall = \Yii::$app->mall;
            $form->setIsLimitBuy(false);
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new Exception('商品不存在');
            }
            $form->goods = $goods;
            $mallGoods = CommonGoods::getCommon()->getMallGoods($goods->id);
            $form->setMember($mallGoods->is_negotiable == 0);
            $form->setShare($mallGoods->is_negotiable == 0);
            $cats = array_column(ArrayHelper::toArray($goods->goodsWarehouse->cats), 'id');
            $cats = array_map(function ($v) {
                return (string)$v;
            }, $cats);
            $res = $form->getAll();
            $res = array_merge($res, [
                'extra_quick_share' => $this->shareQuick($goods, $res['sales']),
                'is_quick_shop' => $mallGoods->is_quick_shop,
                'is_sell_well' => $mallGoods->is_sell_well,
                'is_negotiable' => $mallGoods->is_negotiable,
                //商品分类
                'cats' => $cats
            ]);

            $model = CityDeliverySetting::findOne([
                'key' => 'address', 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id
            ]);
            $this->setLog($goods);
            return $this->success([
                'goods' => $res,
                'delivery' => !empty($model) ? $model->value : ''
            ]);
        } catch (\Exception $e) {
            \Yii::error($e);
            return $this->fail([
                'msg' => $e->getMessage(),
                'errors' => $e
            ]);
        }
    }
}
