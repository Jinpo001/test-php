<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerWorkLog;

class TellerPassportForm extends Model
{
    public $username;
    public $password;
    public $mall_id;
    public $pic_captcha;

    public function rules()
    {
        return [
            [['username', 'password', 'pic_captcha', 'mall_id'], 'required'],
            [['username', 'password', 'pic_captcha'], 'trim'],
            [['pic_captcha'], 'captcha', 'captchaAction' => 'site/pic-captcha'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'pic_captcha' => '验证码',
            'mall_id' => '商城ID',
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->mall_id = base64_decode($this->mall_id);
            $mall = Mall::find()->andWhere(['id' => $this->mall_id, 'is_delete' => 0])->one();

            if (!$mall) {
                throw new \Exception('商城不存在');
            }

            if ($mall->is_disable) {
                throw new \Exception('商城已被禁用');
            }

            $permission = \Yii::$app->branch->childPermission($mall->user->adminInfo);
            $permissionFlip = array_flip($permission);

            if (!isset($permissionFlip['teller'])) {
                throw new \Exception('无收银台权限');
            }

            $userId = User::find()->andWhere(['username' => $this->username, 'mall_id' => $this->mall_id])->select('id');
            $cashier = TellerCashier::find()->andWhere(['user_id' => $userId])->with('user', 'store')->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($this->password, $cashier->user->password)) {
                throw new \Exception('密码错误');
            }

            if (!$cashier->status) {
                throw new \Exception('收银员账号已停用');
            }

            if (!$cashier->store) {
                throw new \Exception('门店不存在');
            }
            $this->setWorkLog($cashier);

            \Yii::$app->setSessionMallId($cashier->user->mall_id);
            \Yii::$app->user->login($cashier->user, 86000);
            setcookie('__login_role', 'cashier');
            setcookie('__mall_id', $cashier->user->mall_id);


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '登录成功',
                'data' => [
                    'url' => 'plugin/teller/web/manage/index'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    // 交班记录
    public function setWorkLog($cashier)
    {
        $common = new CommonTellerSetting();
        $common->mall_id = $cashier->mall_id;
        $setting = $common->search();

        if ($setting['is_shifts']) {
            $workLog = TellerWorkLog::find()->andWhere([
                'mall_id' => $cashier->mall_id,
                'mch_id' => $cashier->mch_id,
                'cashier_id' => $cashier->id,
                'is_delete' => 0,
                'status' => TellerWorkLog::PENDING
            ])->one();

            $extraAttributes = [
                'proceeds' => [
                    'total_proceeds' => '0.00',
                    'total_order' => 0,
                    'wechat_proceeds' => '0.00',
                    'alipay_proceeds' => '0.00',
                    'cash_proceeds' => '0.00',
                    'balance_proceeds' => '0.00',
                    'pos_proceeds' => '0.00',
                ],
                'recharge' => [
                    'total_recharge' => '0.00',
                    'total_order' => 0,
                    'wechat_recharge' => '0.00',
                    'alipay_recharge' => '0.00',
                    'cash_recharge' => '0.00',
                    'pos_recharge' => '0.00',
                ],
                'refund' => [
                    'total_refund' => '0.00',
                    'total_order' => 0,
                    'wechat_refund' => '0.00',
                    'alipay_refund' => '0.00',
                    'cash_refund' => '0.00',
                    'balance_refund' => '0.00',
                    'pos_refund' => '0.00'
                ]
            ];

            if (!$workLog) {
                $workLog = new TellerWorkLog();
                $workLog->mall_id = $cashier->mall_id;
                $workLog->mch_id = $cashier->mch_id;
                $workLog->cashier_id = $cashier->id;
                $workLog->status = TellerWorkLog::PENDING;
                $workLog->store_id = $cashier->store_id;
                $workLog->start_time = date('Y-m-d H:i:s', time());
                $workLog->extra_attributes = json_encode($extraAttributes);
                $res = $workLog->save();
            }
        }
    }
}
