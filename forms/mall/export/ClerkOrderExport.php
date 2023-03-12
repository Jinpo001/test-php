<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\response\ApiCode;
use app\models\Order;
use app\models\OrderDetail;

class ClerkOrderExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'clerk_user_name',
                'value' => '核销员',
            ],
            [
                'key' => 'clerk_store_name',
                'value' => '核销门店',
            ],
            [
                'key' => 'clerk_time',
                'value' => '核销时间',
            ]
        ];

        return $fieldsList;
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query->with('store', 'clerkUser.user.userInfo')->orderBy(['created_at' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $fileName = '核销订单';

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['platform'] = $this->getPlatform($item->clerkUser->user);
            $arr['order_no'] = $item->order_no;
            $arr['clerk_user_name'] = $item->clerkUser->user->nickname;
            $arr['clerk_store_name'] = $item->store->name;
            $arr['clerk_time'] = $item->send_time;
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    protected function getIsAddNumber()
    {
        return false;
    }
}
