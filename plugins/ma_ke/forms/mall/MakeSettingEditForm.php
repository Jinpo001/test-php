<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\ma_ke\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;
use app\plugins\ma_ke\forms\common\MaKeSetting;

class MakeSettingEditForm extends Model
{
    public $status;
    public $app_id;
    public $token;
    public $domain;

    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['app_id', 'token', 'domain'], 'string'],
            [['status', 'app_id', 'token', 'domain'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => "同城速送",
            'app_id' => "APPID",
            'token' => "TOKEN",
            'domain' => "域名",
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->checkData();

            $array = [
                'status' => $this->status,
                'app_id' => $this->app_id,
                'token' => $this->token,
                'domain' => $this->domain,
            ];
            $keyName = MaKeSetting::getInstance()->getKeyName();
            $result = CommonOption::set($keyName, $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new \Exception('保存失败');
            }

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function checkData()
    {

    }
}
