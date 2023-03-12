<?php
/**
 * Created by zjhj_mall_v4
 * User: jack_guo
 * Date: 2019/7/24
 * Email: <657268722@qq.com>
 */

namespace app\plugins\clerk\forms;


use app\core\response\ApiCode;
use app\models\ClerkUser;
use app\models\Model;
use app\models\Order;
use app\models\OrderClerk;
use app\models\UserCard;

class UserClerkForm extends Model
{
    public $clerk_id;

    public function rules()
    {
        return [
            [['clerk_id'], 'required'],
            [['clerk_id'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $clerk_info = ClerkUser::find()->where(['user_id' => $this->clerk_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->with('store')->asArray()->all();
        if (empty($clerk_info)) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '该用户不是核销员'
            ];
        }
        $is_mall = false;
        foreach ($clerk_info as $item) {
            if (!empty($item['store']) && $item['store'][0]['mch_id'] == 0) {
                $is_mall = true;
                continue;
            }
        }

        $is_clerk = false;
        foreach (\Yii::$app->plugin->list as $plugin) {
            if ($plugin->name == 'scan_code_pay') {
                try {
                    $config = \Yii::$app->plugin->getPlugin($plugin->name)->getSetting();
                    $is_clerk = isset($config['is_clerk']) && $config['is_clerk'] ? true : false;
                }catch(\Exception $excetipn) {
                }
            }
        }


        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'is_mall' => $is_mall,//核销卡卷列表开关
                'is_clerk' => $is_clerk,//核销端是否支持扫码支付
            ]
        ];
    }
}