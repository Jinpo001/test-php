<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\live;

use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\forms\mall\live\CommonLive;
use app\models\LivePendantSetting;
use app\models\Model;

class LivePendantEditForm extends Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'required'],
            [['data'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();

            $setting = LivePendantSetting::find()->andWhere(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->one();
            if (!$setting) {
                $setting = new LivePendantSetting();
                $setting->mall_id = \Yii::$app->mall->id;
            }

            $setting->extra_attributes = $this->data;
            $res = $setting->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($setting));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function checkData()
    {

    }

    public function getDefault()
    {
        return [
            'is_open' => 0
        ]; 
    }
}
