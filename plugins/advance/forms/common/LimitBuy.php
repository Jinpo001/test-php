<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/12/17
 * Time: 5:17 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\advance\forms\common;

use app\plugins\advance\models\AdvanceOrder;

class LimitBuy extends \app\forms\common\goods\LimitBuy
{
    protected function getOrderNum($time)
    {
        return AdvanceOrder::find()
            ->where([
                'goods_id' => $this->goods->id,
                'is_delete' => 0,
                'is_cancel' => 0,
                'is_refund' => 0,
                'is_recycle' => 0,
                'user_id' => \Yii::$app->user->id,
            ])
            ->sum('goods_num');
    }
}
