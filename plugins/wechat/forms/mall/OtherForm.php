<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/5
 * Time: 1:42 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\mall;

use app\forms\common\CommonOption;
use app\plugins\wechat\forms\Model;

class OtherForm extends Model
{
    public $list;

    public function rules()
    {
        return [
            ['list', 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $data = [];
        foreach ($this->list as $value) {
            if ($value['check'] == 1) {
                $data[] = $value['key'];
            }
        }
        CommonOption::set('other_config', $data, \Yii::$app->mall->id, 'wechat');
        return $this->success(['msg' => '保存成功']);
    }

    public function getOther()
    {
        return $this->success(['list' => array_values($this->config())]);
    }

    protected function getDefault()
    {
        $default = [
            [
                'key' => 'index',
                'name' => '首页',
                'check' => 0,
                'router' => [
//                    'api/index/tpl-index',
                    'api/index/index-wechat',
                ]
            ],
            [
                'key' => 'order_submit',
                'name' => '商城下单',
                'check' => 0,
                'router' => [
                    'api/order/preview',
                    'api/order/submit'
                ]
            ],
            [
                'key' => 'pond',
                'name' => '九宫格抽奖',
                'check' => 0,
                'permission' => 'pond',
                'router' => [
                    'plugin/pond/api/pond/lottery'
                ]
            ],
            [
                'key' => 'scratch',
                'name' => '刮刮卡抽奖',
                'check' => 0,
                'permission' => 'scratch',
                'router' => [
                    'plugin/scratch/api/scratch/index',
                    'plugin/scratch/api/scratch/receive'
                ]
            ],
            [
                'key' => 'lottery',
                'name' => '幸运抽奖',
                'check' => 0,
                'permission' => 'lottery',
                'router' => [
                    'plugin/lottery/api/lottery/detail'
                ]
            ],
            [
                'key' => 'bargain',
                'name' => '砍价',
                'check' => 0,
                'permission' => 'bargain',
                'router' => [
                    'plugin/bargain/api/order/bargain-submit',
                    'plugin/bargain/api/order/user-join-bargain',
                ]
            ],
            [
                'key' => 'recharge',
                'name' => '充值余额',
                'check' => 0,
                'router' => [
                    'api/recharge/balance-recharge'
                ]
            ],
            [
                'key' => 'receive_coupon',
                'name' => '领取优惠券',
                'check' => 0,
                'permission' => 'coupon',
                'router' => [
                    'api/coupon/receive'
                ]
            ],
            [
                'key' => 'check_in',
                'name' => '签到',
                'check' => 0,
                'permission' => 'check_in',
                'router' => [
                    'plugin/check_in/api/index/sign-in'
                ]
            ],
            [
                'key' => 'fission',
                'name' => '红包墙领取红包',
                'check' => 0,
                'permission' => 'fission',
                'router' => [
                    'plugin/fission/api/fission/unite',
                    'plugin/fission/api/index/wechat',
                ]
            ],
        ];
        $permission = \Yii::$app->mall->role->getPermission();
        $newList = [];
        foreach ($default as $value) {
            if (!isset($value['permission']) || in_array($value['permission'], $permission)) {
                unset($value['permission']);
                $newList[$value['key']] = $value;
            }
        }
        return $newList;
    }

    public function config()
    {
        $res = $this->getDefault();
        $data = CommonOption::get('other_config', \Yii::$app->mall->id, 'wechat', []);
        foreach ($data as $datum) {
            if (isset($res[$datum])) {
                $res[$datum]['check'] = 1;
            }
        }
        return $res;
    }
}
