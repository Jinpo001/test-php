<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\common;

use app\core\payment\Payment;
use app\forms\common\CommonOption;
use app\forms\mall\recharge\RechargeSettingForm;
use app\helpers\ArrayHelper;
use app\helpers\PluginHelper;
use app\models\GoodsCats;
use app\models\Mall;
use app\models\Model;
use app\models\Option;
use app\models\User;
use app\models\UserIdentity;
use app\models\UserInfo;
use app\plugins\teller\forms\common\TellerGoodsEditForm;
use app\plugins\teller\models\TellerPrinterSetting;

/**
 * @property Mall $mall
 */
class CommonTellerSetting extends Model
{
    public $mall_id;

    private $mall;

    private static $tellerSetting;

    public function getMall()
    {
        if ($this->mall_id) {
            $this->mall = Mall::findOne($this->mall_id);
        } else {
            $this->mall = Mall::findOne(\Yii::$app->mall->id);
        }

        // 其它类中可能会调用 \Yii::$app->mall
        \Yii::$app->setMall($this->mall);

        return $this->mall;
    }

    public function search()
    {
        if (self::$tellerSetting) {
            return self::$tellerSetting;
        }

        $this->getMall();
        $defaultSetting = $this->getDefault();
        $setting = CommonOption::get('teller_setting', $this->mall->id, Option::GROUP_ADMIN);

        if ($setting) {
            foreach ($defaultSetting as $key => $value) {
                if (is_array($value)) {
                    $setting[$key] = isset($setting[$key]) ? $setting[$key] : $value;
                } else if (is_float($value)) {
                    $setting[$key] = isset($setting[$key]) ? (float)price_format($setting[$key]) : $value;
                } else if (is_numeric($value)) {
                    $setting[$key] = isset($setting[$key]) ? (int)$setting[$key] : $value;
                } else {
                    $setting[$key] = isset($setting[$key]) ? $setting[$key] : $value;
                }
            }
        } else {
            $setting = $defaultSetting;
        }

        // 余额支付方式
        $setting['balance_type_list'] = [Payment::PAY_BALANCE_TYPE_QR_CODE];
        $rechargeForm = new RechargeSettingForm();
        $rechargeSetting = $rechargeForm->setting();
        if ($rechargeSetting['is_pay_password']) {
            $setting['balance_type_list'][] = Payment::PAY_BALANCE_TYPE_PASSWORD;
        }

        // 创建收银台匿名用户
        $setting = $this->createUser($setting);

        // 创建收银台商品
        $setting = $this->createGoods($setting);

        // 超级会员卡权限
        $permission = \Yii::$app->branch->childPermission($this->mall->user->adminInfo);
        $permissionFlip = array_flip($permission);
        
        if (!isset($permissionFlip['vip_card'])) {
            $setting['svip_status'] = -1;
        } else {
            $setting['svip_status'] = $setting['svip_status'] == -1 ? 1 : $setting['svip_status'];
        }

        // 分销权限
        if (!isset($permissionFlip['share'])) {
            $setting['is_share'] = -1;
        } else {
            $setting['is_share'] = $setting['is_share'] == -1 ? 1 : $setting['is_share'];
        }

        // 满减权限
        if (!isset($permissionFlip['full-reduce'])) {
            $setting['is_full_reduce'] = -1;
        } else {
            $setting['is_full_reduce'] = $setting['is_full_reduce'] == -1 ? 1 : $setting['is_full_reduce'];
        }

        // 余额支付权限
        if (!$rechargeSetting['status']) {
            $setting['is_balance'] = -1;
        } else {
            $setting['is_balance'] = $setting['is_balance'] == -1 ? 1 : $setting['is_balance'];
        }

        // 余额密码支付权限
        if (!$rechargeSetting['is_pay_password']) {
            $setting['is_balance_pay_password'] = -1;
        } else {
            $setting['is_balance_pay_password'] = $setting['is_balance_pay_password'] == -1 ? 1 : $setting['is_balance_pay_password'];
        }

        if (\Yii::$app instanceof \yii\web\Application) {
            $host = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl;
            $mallId = base64_encode($this->mall->id);
            $loginUrl = sprintf('%s/index.php?r=plugin/teller/web/passport/login&mall_id=%s', $host, $mallId);

            $setting['login_url'] = $loginUrl; // 登录入口
        }

        if (($setting['is_goods_change_price_type'] == 1 && !$setting['most_plus'] && !$setting['most_subtract']) || ($setting['is_goods_change_price_type'] == 2 && !$setting['most_plus_percent'] && !$setting['most_subtract_percent'])) {
            $setting['is_goods_change_price'] = 0;
        }

        $cats = GoodsCats::find()->andWhere(['id' => $setting['tab_list']])->all();
        $newCats = [];
        foreach ($cats as $cat) {
            $newCats[$cat->id] = [
                'label' => $cat->name,
                'value' => (string)$cat->id
            ];
        }

        $newTabList = [];
        foreach ($setting['tab_list'] as $item) {
            $newTabList[] = $newCats[$item];
        }
        
        $setting['new_tab_list'] = $newTabList;

        $setting['payment_type'] = [];
        if ($setting['is_wechat_pay'] == 1 && $setting['wechat_pay_id']) {
            $setting['payment_type'][] = Payment::PAY_TYPE_WECHAT_SCAN;
        }

        if ($setting['is_ali_pay'] == 1 && $setting['ali_pay_id']) {
            $setting['payment_type'][] = Payment::PAY_TYPE_ALIPAY_SCAN;
        }

        if ($setting['is_balance'] == 1) {
            $setting['payment_type'][] = Payment::PAY_TYPE_BALANCE;
        }

        if ($setting['is_cash'] == 1) {
            $setting['payment_type'][] = Payment::PAY_TYPE_CASH;
        }

        if ($setting['is_pos'] == 1) {
            $setting['payment_type'][] = Payment::PAY_TYPE_POS;
        }

        $printer = TellerPrinterSetting::findOne(['mall_id' => $this->mall->id, 'status' => 1, 'id' => $setting['shifts_print']]);
        if (!$printer) {
            $setting['shifts_print'] = null;
        }

        self::$tellerSetting = $setting;

        return $setting;
    }

    public function getDefault()
    {
        $this->getMall();
        
        $default = [
            'is_shifts' => 0,// 是否开启交班
            'shifts_print' => 0,// 交班打印机
            'is_member_topup' => 0,// 是否开启会员充值
            'is_add_money' => 0,// 是否开启加钱开关
            'is_tab' => 0,// 是否开启tab标签
            'tab_list' => [], // tab标签列表

            'is_coupon' => 1, // 是否使用优惠券
            'svip_status' => 1, // -1.未安装超级会员卡 1.开启 0.关闭
            'is_member_price' => 1, // 是否使用会员价
            'is_integral' => 1, // 是否使用积分
            'is_full_reduce' => 1, // 是否优惠满减
            'is_share' => 0,

            'is_price' => 0, // 是否开启抹零设置
            'price_type' => 1,// 1.抹分|2.抹角|3.四舍分|4.五入到角

            'is_cashier_push' => 0, // 是否开启收银员提成
            'cashier_push_type' => 1,// 1.按订单|2.按金额百分比
            'cashier_push' => 0.00, // 收银员提成按订单 
            'cashier_push_percent' => 0.00, // 收银员提成按金额百分比

            'is_sales_push' => 0, // 是否开启导购员提成
            'sales_push_type' => 1, // 1.按订单|2.按金额百分比
            'sales_push' => 0.00, // 导购员提成按订单 
            'sales_push_percent' => 0.00, // 导购员提成按金额百分比

            'is_goods_change_price' => 0, // 是否开启商品改价
            'is_goods_change_price_type' => 1, // 1.固定金额|2.百分比
            'most_plus' => 0.00, //最多可加金额
            'most_subtract' => 0.00, //最多可减金额
            'most_plus_percent' => 0, //最多可加金额 百分比
            'most_subtract_percent' => 0, //最多可减金额 百分比

            'user_id' => 0, // 收银台匿名用户ID
            'goods_id' => 0, // 收银台加价商品ID

            'payment_type' => [],
            'is_wechat_pay' => 0, // 微信支付
            'wechat_pay_id' => 0,
            'is_ali_pay' => 0, // 支付宝支付
            'ali_pay_id' => 0,
            'is_balance' => 0, // 余额支付
            'is_balance_pay_password' => 0, // 余额支付密码
            'balance_type_list' => [],
            'is_cash' => 0, // 现金支付
            'is_pos' => 0, // pos机支付
        ];

        if (\Yii::$app instanceof \yii\web\Application) {
            $baseUrl = PluginHelper::getPluginBaseAssetsUrl('teller') . '/img';
            $default['logo_url'] = $baseUrl . '/logo.png';
            $default['background_image_url'] = $baseUrl . '/bg.png';
            $default['copyright'] = '';
            $default['copyright_url'] = '';
        }

        return $default;
    }

    // 收银台创建匿名用户
    private function createUser($setting)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $user = User::find()->andWhere([
                'mall_id' => $this->mall->id,
                'nickname' => '收银台匿名用户',
                'username' => 'teller'
            ])->one();

            if (!$user) {
                $user = new User();
                $user->mall_id = $this->mall->id;
                $user->access_token = \Yii::$app->security->generateRandomString();
                $user->auth_key = \Yii::$app->security->generateRandomString();
                $user->nickname = '收银台匿名用户';
                $user->username = 'teller';
                $user->password = \Yii::$app->getSecurity()->generatePasswordHash('password');
                $res = $user->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($user));
                }

                $userIdentity = new UserIdentity();
                $userIdentity->user_id = $user->id;
                $res = $userIdentity->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($userIdentity));
                }

                $userInfo = new UserInfo();
                $userInfo->user_id = $user->id;
                $res = $userInfo->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($userInfo));
                }
            }

            if (!$user->mobile) {
                $user->mobile = '13888888888';
                $res = $user->save();

                if (!$res) {
                    throw new \Exception($this->getErrorMsg($user));
                }
            }

            $setting['user_id'] = $user->id;

            CommonOption::set('teller_setting', $setting, $this->mall->id, Option::GROUP_ADMIN);

            $transaction->commit();

            return $setting;
        
        }catch(\Exception $exception) {
            $transaction->rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    private function createGoods($setting)
    {
        try {
            $form = new TellerGoodsEditForm();
            $goods = $form->save();

            $setting['goods_id'] = $goods->id;

            CommonOption::set('teller_setting', $setting, $this->mall->id, Option::GROUP_ADMIN);

            return $setting;
        }catch(\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
