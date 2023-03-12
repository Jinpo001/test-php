<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\forms\mall\share\ShareCustomForm;
use app\models\Order;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;

class UserExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'platform',
                'value' => '所属平台',
            ],
            [
                'key' => 'id',
                'value' => '用户ID',
            ],
            [
                'key' => 'platform_user_id',
                'value' => '平台标识ID',
            ],
            [
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'mobile',
                'value' => '绑定手机号',
            ],
            [
                'key' => 'contact_way',
                'value' => '联系方式',
            ],
            [
                'key' => 'remark',
                'value' => '备注',
            ],
            [
                'key' => 'remark_name',
                'value' => '备注名',
            ],
            [
                'key' => 'created_at',
                'value' => '加入时间',
            ],
            [
                'key' => 'member_level',
                'value' => '会员身份',
            ],
            [
                'key' => 'order_count',
                'value' => '订单数',
            ],
            [
                'key' => 'coupon_count',
                'value' => '优惠券总数',
            ],
            [
                'key' => 'card_count',
                'value' => '卡券总数',
            ],
            [
                'key' => 'integral',
                'value' => '积分',
            ],
            [
                'key' => 'balance',
                'value' => '余额',
            ],
            [
                'key' => 'consume_count',
                'value' => '总消费',
            ],
            [
                'key' => 'parent_id_user',
                'value' => '用户推荐人',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $consumeCount = Order::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'is_confirm' => 1
        ])
            ->andWhere('user_id = u.id')
            ->select('sum(total_pay_price)');

        $query->with(['userInfo', 'identity', 'newParent'])
            ->addSelect([
                'consume_count' => $consumeCount
            ])
            ->orderBy(['created_at'=> SORT_DESC]);

        $this->exportAction($query, ['is_array' => true]);

        return true;
    }

    public function getFileName()
    {
        return '用户列表';
    }

    protected function transform($list)
    {
        $userIds = [];
        foreach ($list as $item) {
            $userIds[] = $item['user_id'];
        }

        $users =  User::find()->andWhere(['id' => $userIds])->with('userInfo')->all();
        $userList = [];
        foreach ($users as $user) {
            $userList[$user->id] = $user;
        }


        $newList = [];
        $number = 1;
        $members = CommonMallMember::getAllMember();

        $return = (new ShareCustomForm())->getData()['data'];
        $parentName = $return['words']['head_office']['name'] ?: '总店';

        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['platform'] = $this->getPlatform($userList[$item['user_id']]);
            $arr['id'] = $item['user_id'];
            $arr['platform_user_id'] = $item['platform_user_id'];
            $arr['nickname'] = $item['nickname'];
            $arr['mobile'] = $item['mobile'];
            $arr['contact_way'] = $item['contact_way'];
            $arr['remark'] = $item['remark'];
            $arr['remark_name'] = $item['remark_name'];
            $arr['created_at'] = $item['created_at'];

            $memberLevel = $item['member_level'];
            if ($memberLevel > 0) {
                foreach ($members as $member) {
                    if ($member['level'] == $memberLevel) {
                        $arr['member_level'] = $member['name'];
                        break;
                    }
                }
            } elseif ($memberLevel == 0) {
                $arr['member_level'] = '普通用户';
            } else {
                $arr['member_level'] = '未知';
            }
            $arr['order_count'] = (int)$item['order_count'];
            $arr['card_count'] = (int)$item['card_count'];
            $arr['coupon_count'] = (int)$item['coupon_count'];
            $arr['integral'] = (int)$item['integral'];
            $arr['balance'] = (float)$item['balance'];
            $arr['consume_count'] = (int)$item['consume_count'];
            $arr['parent_id_user'] = empty($item['newParent']) ? $parentName : $item['newParent']['nickname'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
