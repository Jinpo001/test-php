<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/25
 * Time: 4:50 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\api;

class OrderGoodsAttr extends \app\forms\api\order\OrderGoodsAttr
{
    public function setGoodsAttr($goodsAttr)
    {
        parent::setGoodsAttr($goodsAttr);
        $this->price = 0;
    }
}
