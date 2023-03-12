<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\models\Order;
use app\models\UserCard;
use app\models\UserCoupon;

class MemberLogExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'pay_price',
                'value' => '支付金额',
            ],
            [
                'key' => 'pay_time',
                'value' => '支付日期',
            ],
            [
                'key' => 'detail',
                'value' => '购买情况',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query->orderBy('created_at desc')->with('user.userInfo');

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '会员购买记录';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item->user);
            $arr['order_no'] = $item->order_no;
            $arr['nickname'] = $item->user->nickname;
            $arr['pay_price'] = (float)$item->pay_price;
            $arr['pay_time'] = $this->getDateTime($item->pay_time);

            $detail = json_decode($item->detail, true);
            if (isset($detail['before_update']) && isset($detail['after_update'])) {
                $arr['detail'] = $detail['before_update']['name'] . '->' . $detail['after_update'][count($detail['after_update']) - 1]['name'];
            }
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
