<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/20
 * Time: 9:30 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\common;

use app\forms\common\ecard\CheckGoods;
use app\models\EcardOptions;
use app\models\EcardOrder;
use app\models\Goods;
use app\plugins\fission\models\FissionRewardLog;
use yii\helpers\Json;

class CommonEcard extends \app\forms\common\ecard\CommonEcard
{
    /**
     * @param FissionRewardLog $log
     * @throws \Exception
     * @return bool
     * 中奖时，占用卡密数据
     */
    public function setEcardFission($log)
    {
        $reward = Json::decode($log->reward, true);
        if ($reward['status'] !== 'goods') {
            \Yii::warning('奖励不是赠品');
            return false;
        }
        /* @var Goods $goods */
        $goods = Goods::find()->with(['goodsWarehouse'])->where(['id' => $reward['model_id']])->one();
        if ($goods->goodsWarehouse->type !== 'ecard') {
            \Yii::warning('不是卡密商品');
            return false;
        }
        $ecard = $this->getEcard($goods->goodsWarehouse->ecard_id);
        /* @var EcardOptions[] $list */
        $list = $this->getEcardOptions($ecard, 1);
        if (count($list) != 1) {
            throw new \Exception('卡密数据库存不足');
        }
        $data = [];
        $model = new EcardOrder();
        foreach ($list as $item) {
            $data[] = [
                'id' => null,
                'mall_id' => \Yii::$app->mall->id,
                'ecard_id' => $item->ecard_id,
                'value' => $item->value,
                'order_id' => 0,
                'order_detail_id' => 0,
                'is_delete' => 0,
                'token' => $item->token,
                'ecard_options_id' => $item->id,
                'user_id' => $log->user_id,
                'order_token' => $log->token,
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert(
            EcardOrder::tableName(),
            array_keys($model->attributes),
            $data
        )->execute();
        EcardOptions::updateAll(['is_sales' => 1], ['id' => array_column($list, 'id')]);
        $this->updateStock($ecard);
        $this->log(new CheckGoods([
            'ecard' => $ecard,
            'status' => CheckGoods::STATUS_SALES,
            'sign' => 'fission',
            'number' => 1,
            'goods_id' => $goods->id,
        ]));
        return true;
    }
}
