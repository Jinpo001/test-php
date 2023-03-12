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

class ClerkCardExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'card_id',
                'value' => '卡券ID',
            ],
            [
                'key' => 'card_name',
                'value' => '卡券名称',
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
                'key' => 'clerk_number',
                'value' => '核销次数',
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
        $query->with('user','store','userCard.card')->orderBy(['clerked_at' => SORT_DESC]);

        $this->exportAction($query);

        return true;
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $fileName = '核销卡券';

        return $fileName;
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['card_id'] = $item->userCard->card_id;
            $arr['card_name'] = $item->userCard->name;
            $arr['clerk_user_id'] = $item->user->id;
            $arr['clerk_user_name'] = $item->user->nickname;
            $arr['clerk_user_avatar'] = $item->user->userInfo->avatar;
            $arr['clerk_user_platform'] = $item->user->userInfo->platform;
            $arr['clerk_store_name'] = $item->store->name;
            $arr['clerk_number'] = $item->use_number;
            $arr['clerk_time'] = $item->clerked_at;

            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    protected function getIsAddNumber()
    {
        return false;
    }
}
