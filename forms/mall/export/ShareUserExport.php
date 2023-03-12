<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\common\CommonMallMember;
use app\forms\common\share\CommonShareTeam;
use app\forms\mall\share\ShareCustomForm;
use app\models\CoreFile;
use app\models\Order;
use app\models\Share;
use app\models\User;
use app\models\UserCard;
use app\models\UserCoupon;
use app\models\UserInfo;
use yii\helpers\ArrayHelper;

class ShareUserExport extends BaseExport
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
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'name',
                'value' => '姓名',
            ],
            [
                'key' => 'mobile',
                'value' => '手机号',
            ],
            [
                'key' => 'apply_at',
                'value' => '申请时间',
            ],
            [
                'key' => 'status',
                'value' => '审核状态',
            ],
            [
                'key' => 'total_money',
                'value' => '累计佣金',
            ],
            [
                'key' => 'money',
                'value' => '可提现佣金',
            ],
            [
                'key' => 'order_count',
                'value' => '订单数',
            ],
            [
                'key' => 'lower_user',
                'value' => '下级用户',
            ],
            [
                'key' => 'parent_name',
                'value' => '推荐人',
            ],
            [
                'key' => 'remark',
                'value' => '备注信息',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $this->exportAction($query);
    }

    public function getFileName()
    {
        return '分销商列表';
    }

    protected function transform($list)
    {
        $newList = [];
        $form = new CommonShareTeam();
        $form->mall = \Yii::$app->mall;

        $return = (new ShareCustomForm())->getData()['data'];
        $parentName = $return['words']['head_office']['name'] ?: '总店';

        foreach ($list as $item) {
            /* @var Share $item */
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;
            /* @var UserInfo $userInfo */
            $userInfo = $item->userInfo;

            $form->userInfo = $userInfo;
            $first = count($form->info($item->user_id, 1));
            $second = count($form->info($item->user_id, 2));
            $third = count($form->info($item->user_id, 3));
            $arr = [];
            $arr['platform'] = $this->getPlatform($user);
            $arr['id'] = $item->user_id;
            $arr['nickname'] = $user ? $user->nickname : '';
            $arr['name'] = $item->name;
            $arr['mobile'] = $item->mobile;
            $arr['apply_at'] = $this->getDateTime($item->apply_at);
            switch ($item->status) {
                case 0:
                    $arr['status'] = '审核中';
                    break;
                case 1:
                    $arr['status'] = '审核通过';
                    break;
                case 2:
                    $arr['status'] = '审核不通过';
                    break;
                default:
                    break;
            }
            $arr['total_money'] = (float)$item->total_money;
            $arr['money'] = (float)$item->money;
            $arr['order_count'] = (int)count($item->order);
            $arr['parent_name'] = $userInfo->parent ? $userInfo->parent->nickname : $parentName;
            $arr['remark'] = $item->content;
            $arr['lower_user'] = '一级:' . $first . '二级:' . $second . '三级:' . $third;

            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
