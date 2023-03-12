<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/28
 * Time: 4:21 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\forms\mall\export\BaseExport;
use app\plugins\fission\forms\common\CommonActivity;

class ListExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'name',
                'value' => '活动名称',
            ],
            [
                'key' => 'user',
                'value' => '用户信息',
            ],
            [
                'key' => 'first_status',
                'value' => '红包种类',
            ],
            [
                'key' => 'first_number',
                'value' => '红包金额',
            ],
            [
                'key' => 'share_number',
                'value' => '已发放红包数量',
            ],
            [
                'key' => 'last_share_number',
                'value' => '剩余红包数量',
            ],
            [
                'key' => 'current_level',
                'value' => '当前关卡',
            ],
            [
                'key' => 'rewards',
                'value' => '关卡奖励',
            ],
            [
                'key' => 'created_at',
                'value' => '领取时间',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $query = $query->select('id,mall_id,user_id,activity_id,invite_activity_log_id,invite_user_id,activity,select_name')
            ->orderBy('created_at desc')->with(['user']);

        $this->exportAction($query);
    }

    protected function transform($activity)
    {
        $logForm = new LogForm();
        $list = $logForm->getReturn($activity);
        $statusList = CommonActivity::getInstance()->statusList();
        $levelList = ['','关卡一','关卡二','关卡三','关卡四','关卡五'];
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['name'] = $item['name'];
            $arr['user'] = sprintf('(%d)%s', $item['user_id'], $item['nickname']);
            $arr['first_status'] = $statusList[$item['first_status']];
            $arr['first_number'] = $item['first_status'] == 'coupon' ? '-' : floatval($item['first_number']);
            $arr['share_number'] = $item['share_number'];
            $arr['last_share_number'] = intval($item['last_share_number']);
            $arr['current_level'] = $levelList[$item['current_level']];
            $arr['rewards'] = $this->getReward($item['rewards']);
            $arr['created_at'] = $item['created_at'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        return '红包墙记录';
    }

    public function getReward($reward)
    {
        switch ($reward['status']) {
            case 'cash':
                return '￥' . $reward['real_reward'] . '现金金额';
                break;
            case 'balance':
                return $reward['real_reward'] . '商城余额';
                break;
            case 'integral':
                return $reward['real_reward'] . '商城积分';
                break;
            case 'goods':
                return $reward['goods']['name'];
                break;
            case 'coupon':
                return $reward['coupon']['name'];
                break;
            case 'card':
                return $reward['card']['name'];
                break;
            default:
                return '';
        }
    }
}
