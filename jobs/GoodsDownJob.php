<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/20
 * Time: 11:40 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;

use app\models\Goods;
use app\models\Mall;
use yii\queue\JobInterface;

class GoodsDownJob extends BaseJob implements JobInterface
{
    /**
     * @var Goods
     */
    public $goods;

    /**
     * @var Mall $mall
     */
    public $mall;

    public $sell_end_time;

    /**
     * @param \yii\queue\Queue $queue
     * @return mixed|void
     * 商品销售时间结束自动下架
     */
    public function execute($queue)
    {
        try {
            $this->setRequest();
            $mall = Mall::findOne($this->mall->id);
            \Yii::$app->setMall($mall);
            $goods = Goods::findOne($this->goods->id);
            if (strtotime($goods->sell_end_time) !== strtotime($this->sell_end_time)) {
                throw new \Exception('商品销售时间已被更改');
            }
            $goods->status = 0;
            $goods->save();
        } catch (\Exception $exception) {
            \Yii::warning('--商品自动下架' . $this->goods->id . '--');
            \Yii::warning($exception);
        }
    }
}
