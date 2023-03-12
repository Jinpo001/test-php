<?php

namespace app\forms\mall\recharge;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Recharge;
use app\plugins\bargain\models\Code;

class RecharegConfigForm extends Model
{

    public $config;
    public $customize;


    public function rules()
    {
        return [
            [['config', 'customize'], 'trim'],
        ];
    }

    public function get()
    {
        try {
            $setting = (new RechargeSettingForm())->setting();
            $c = (new RechargePageForm())->get();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'setting' => [
                        'config' => $setting,
                        'customize' => array_shift($c)
                    ],
                    'selectList' => array_shift($c),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function post()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $t = \Yii::$app->db->beginTransaction();
            $recharge = new RechargeSettingForm();
            $recharge->attributes = $this->config;
            $return = $recharge->set();
            if (isset($return['code']) && $return['code'] !== ApiCode::CODE_SUCCESS) {
                throw new \Exception($return['msg']);
            }

            $recharge = new RechargePageForm();
            $recharge->attributes = $this->customize;
            $return = $recharge->post();
            if (isset($return['code']) && $return['code'] !== ApiCode::CODE_SUCCESS) {
                throw new \Exception($return['msg']);
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }
}