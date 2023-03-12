<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\user_center;

use app\core\response\ApiCode;
use app\forms\common\config\UserCenterConfig;
use app\forms\common\platform\PlatformConfig;
use app\models\Model;
use app\models\Option;
use app\models\UserCenter;

class UserCenterForm extends Model
{
    public function getDetail()
    {
        $id = \Yii::$app->request->get('id');
        $userCenter = UserCenterConfig::getInstance()->getEdit($id);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $userCenter,
            ]
        ];
    }

    public function getDefault()
    {
        $iconUrlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/user-center/';
        return [
            'is_account_status' => '1',
            'is_menu_status' => '1',
            'is_order_bar_status' => '1',
            'is_foot_bar_status' => '1',
            'menu_style' => '1',
            'top_style' => '1',
            'top_pic_url' => $iconUrlPrefix . 'img-user-bg.png',
            'user_name_color' => '#ffffff',
            'member_pic_url' => $iconUrlPrefix . 'icon-member.png',
            'member_bg_pic_url' => $iconUrlPrefix . 'card-member-0.png',
            'style_bg_pic_url' => $iconUrlPrefix . 'img-user-bg.png',
            'address' => [
                'status' => 1,
                'bg_color' => '#ff4544',
                'text_color' => '#FFFFFF',
                'pic_url' => $iconUrlPrefix . 'address-white.png',
            ],
            'account' => [ // TODO 好像是废弃不用了
                [
                    'icon_url' => $iconUrlPrefix . 'icon-wallet.png',
                    'name' => '我的钱包',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-integral.png',
                    'name' => '积分',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-balance.png',
                    'name' => '余额',
                ],
            ],
            'menus' => [],
            'order_bar' => [
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-0.png',
                    'name' => '待付款',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-1.png',
                    'name' => '待发货',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-2.png',
                    'name' => '待收货',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-3.png',
                    'name' => '已完成',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'icon-order-4.png',
                    'name' => '售后',
                ],
            ],
            'foot_bar' => [
                [
                    'icon_url' => $iconUrlPrefix . 'favorite.png',
                    'name' => '我的收藏',
                ],
                [
                    'icon_url' => $iconUrlPrefix . 'foot.png',
                    'name' => '我的足迹',
                ],
            ],
            'account_bar' => [
                'status' => '1',
                'integral' => [
                    'status' => '1',
                    'text' => '积分',
                    'icon' => $iconUrlPrefix . 'icon-integral.png',
                ],
                'balance' => [
                    'status' => '1',
                    'text' => '余额',
                    'icon' => $iconUrlPrefix . 'icon-balance.png',
                ],
                'coupon' => [
                    'status' => '1',
                    'text' => '优惠券',
                    'icon' => $iconUrlPrefix . 'icon-coupon.png',
                ],
                'card' => [
                    'status' => '1',
                    'text' => '卡券',
                    'icon' => $iconUrlPrefix . 'icon-card.png',
                ],
            ],
            'general_user_text' => '普通用户'
        ];
    }

    public $type;
    public $page;

    public function rules()
    {
        return [
            ['type', 'string'],
            ['type', 'in', 'range' => ['', 'recycle']],
            ['page', 'integer'],
        ];
    }

    public function getList()
    {
        $this->compatible();
        /** @var UserCenter[] $list */
        $list = UserCenter::find()
            ->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])
            ->keyword($this->type == 'recycle', ['is_recycle' => 1])
            ->keyword($this->type == '', ['is_recycle' => 0])
            ->page($pagination, 20, $this->page)
            ->all();
        $newList = [];
        $platformIconList = PlatformConfig::getPlatformIconUrl(true);
        $platformIconList = array_column($platformIconList, null, 'key');
        foreach ($list as $item) {
            $platform = $item->platform ? explode(',', $item->platform) : [];
            $newPlatform = [];
            foreach ($platform as $value) {
                if (!isset($platformIconList[$value])) {
                    continue;
                }
                $newPlatform[]['icon'] = $platformIconList[$value]['icon'];
            }
            $newList[] = [
                'id' => $item->id,
                'platform' => $newPlatform,
                'name' => $item->name,
            ];
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination
            ]
        ];
    }

    public function compatible()
    {
        $model = Option::findOne([
            'name' => Option::NAME_USER_CENTER,
            'mall_id' => \Yii::$app->mall->id,
            'group' => Option::GROUP_APP,
            'mch_id' => 0,
        ]);
        if (!$model) {
            return false;
        }

        $setting = UserCenterConfig::getInstance()->getSetting();
        $userCenter = new UserCenter();
        $userCenter->mall_id = \Yii::$app->mall->id;
        $userCenter->is_delete = 0;
        $userCenter->is_recycle = 0;
        $userCenter->config = \Yii::$app->serializer->encode($setting);
        $userCenter->name = '用户中心';
        $userCenter->save();

        $platform = PlatformConfig::getInstance()->getPlatformIconUrl(true);
        $platformList = array_column($platform, 'key');
        $operate = new OperateForm();
        $operate->setPlatform($userCenter, $platformList);
        $model->name = 'old_' . Option::NAME_USER_CENTER;
        $model->save();
        return true;
    }
}
