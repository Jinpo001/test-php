<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\forms\common\coupon\CommonCouponList;
use app\forms\common\platform\PlatformConfig;
use app\helpers\ArrayHelper;
use app\models\FullReduceActivity;
use app\models\MallMembers;
use app\models\Model;
use app\models\QrCodeParameter;
use app\models\User;
use app\models\UserCoupon;
use app\models\UserIdentity;
use app\plugins\teller\forms\web\order\TellerOrderSubmitForm;
use app\plugins\vip_card\forms\common\CommonVip;

class MemberForm extends Model
{
    public $keyword;
    public $form_data;

    public function rules()
    {
        return [
            [['keyword'], 'required'],
            [['keyword'], 'string'],
            [['keyword', 'form_data'], 'trim'],
        ];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $query = User::find()->alias('u')->andWhere([
                'u.mall_id' => \Yii::$app->mall->id,
                'u.mch_id' => 0,
                'u.is_delete' => 0
            ])
                ->joinWith(['userInfo', 'identity AS i' => function($query) {
                    $query->andWhere([
                        'i.is_operator' => 0,
                    ]);
                }]);

            if (mb_strlen($this->keyword) > 11) {
                $userIds = QrCodeParameter::find()->alias('qp')->andWhere(['qp.token' => $this->keyword])->select('user_id');
                $query->andWhere(['u.id' => $userIds]);
            } else {
                $query->andWhere(['u.mobile' => $this->keyword]);
            }

            $users = $query->with('identity.member')->page($pagination)->all();

            $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
            $permissionFlip = array_flip($permission);
            $isPlugins = isset($permissionFlip['vip_card']) ? true : false;

            $platformConfig = new PlatformConfig();
            $list = array_map(function($user) use($isPlugins, $platformConfig){
                $isVip = false;
                $discount = null;
                if ($isPlugins) {
                    $data = (new CommonVip())->getUserInfo($user);
                    $isVip = $data['is_vip_card_user'] ? true : false;

                    if (isset($data['vip_card_user']['image_discount'])) {
                        $discount = $data['vip_card_user']['image_discount'];
                    }
                }

                $couponCount = UserCoupon::find()->andWhere([
                    'user_id' => $user->id,
                    'is_delete' => 0,
                    'is_use' => 0
                ])
                    ->andWhere(['>', 'end_time', mysql_timestamp()])
                    ->count();

                return [
                    'user_id' => $user->id,
                    'nickname' => $user->nickname,
                    'mobile' => $user->mobile,
                    'avatar' => $user->userInfo->avatar,
                    'integral' => $user->userInfo->integral,
                    'balance' => $user->userInfo->balance,
                    'is_pay_password' => $user->userInfo->pay_password ? 1 : 0,
                    'member_name' => $user->identity && $user->identity->member ? $user->identity->member->name : '普通会员',
                    'coupon_count' => (int)$couponCount,
                    'is_vip' => $isVip,
                    'vip_discount' => $discount,
                    'platform' => $platformConfig->getPlatformIcon($user)
                ];
            }, $users);
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination,
                ],
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }
}
