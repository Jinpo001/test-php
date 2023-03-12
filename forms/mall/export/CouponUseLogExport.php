<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\core\response\ApiCode;
use app\models\Order;
use app\models\UserCoupon;
use yii\helpers\BaseJson;

class CouponUseLogExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '用户昵称',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'coupon_name',
                'value' => '优惠券名',
            ],
            [
                'key' => 'lowest_monery',
                'value' => '最低消费金额',
            ],
            [
                'key' => 'preferential_mode',
                'value' => '优惠方式',
            ],
            [
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query = $query->with(['user', 'userCoupon'])->orderBy(['id' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '优惠券使用记录';
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['status'] = $item->cancel_status == 1 ? '已退回' : '已使用';
            $arr['platform'] = $this->getPlatform($item->user);
            $arr['nickname'] = $item['user']['nickname'];
            $arr['user_id'] = $item['user']['id'];
            $arr['order_no'] = $item->order_no;
            $arr['created_at'] = $item->created_at;

            $arr['coupon_name'] = \yii\helpers\BaseJson::decode($item->userCoupon->coupon_data)['name'];
            $arr['lowest_monery'] = $item->userCoupon->coupon_min_price;
            if ($item->userCoupon->type == 2) {
                $arr['preferential_mode'] = '优惠:' . $item->userCoupon->sub_price;
            } else {
                $arr['preferential_mode'] = $item->userCoupon->discount . '折';
            }

            if ($item->userCoupon->type == 1 && $item->userCoupon->discount_limit) {
                $arr['preferential_mode'] = $arr['preferential_mode'] . ' 优惠上限: ' . $item->userCoupon->discount_limit; 
            }
            
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    protected function getIsAddNumber()
    {
        return false;
    }
}
