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

class BalanceLogExport extends BaseExport
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
                'key' => 'mobile',
                'value' => '用户手机号',
            ],
            [
                'key' => 'type',
                'value' => '金额类型',
            ],
            [
                'key' => 'money',
                'value' => '金额',
            ],
            [
                'key' => 'created_at',
                'value' => '支付日期',
            ],
            [
                'key' => 'desc',
                'value' => '说明',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query = $query->orderBy('created_at')->with(['user.userInfo']);
        
        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '余额记录';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($item->user);
            $arr['desc'] = $item->desc;
            $arr['nickname'] = $item->user->nickname;
            $arr['user_id'] = $item->user->id;
            $arr['mobile'] = $item->user->mobile;
            $arr['money'] = (float)$item->money;
            $arr['created_at'] = $this->getDateTime($item->created_at);
            $arr['type'] = $item->type == 1 ? '收入' : '支出';
            $arr['order_no'] = $item->order_no;
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
