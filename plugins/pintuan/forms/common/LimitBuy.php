<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/12/29
 * Time: 10:08 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\pintuan\forms\common;

use app\models\Order;
use app\models\OrderDetail;
use app\plugins\pintuan\models\PintuanGoods;

class LimitBuy extends \app\forms\common\goods\LimitBuy
{
    public function getOrderNum($time)
    {
        $pGoods = PintuanGoods::findOne(['goods_id' => $this->goods->id]);
        if ($pGoods->pintuan_goods_id == 0) {
            $goodsIds = PintuanGoods::find()
                ->where(['pintuan_goods_id' => $pGoods->id])
                ->select('goods_id')->column();
            array_push($goodsIds, $this->goods->id);
        } else {
            $goodsIds = PintuanGoods::find()
                ->andWhere([
                    'or',
                    ['pintuan_goods_id' => $pGoods->pintuan_goods_id],
                    ['id' => $pGoods->pintuan_goods_id]
                ])
                ->select('goods_id')->column();
        }
        return OrderDetail::find()->alias('od')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where([
                'od.goods_id' => $goodsIds,
                'od.is_delete' => 0,
                'o.user_id' => \Yii::$app->user->id,
                'o.is_delete' => 0,
            ])
            ->keyword($time, ['between', 'o.created_at', $time, mysql_timestamp()])
            ->andWhere(['!=', 'o.cancel_status', 1])
            ->sum('od.num');
    }
}
