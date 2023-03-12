<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\forms\api;


use app\core\response\ApiCode;
use app\forms\common\CommonQrCode;
use app\models\ClerkUser;
use app\models\Model;

class QrCodeForm extends Model
{
    public $price;
    public $is_clerk_enter;

    public function rules()
    {
        return [
            [['price', 'is_clerk_enter'], 'number']
        ];
    }

    public function attributeLabels()
    {
        return [
            'price' => '金额',
            'is_clerk_enter' => '核销员端请求'
        ];
    }

    public function getQrCode()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $data = ['user_id' => \Yii::$app->user->id];

            if ($this->price > 100000) {
                throw new \Exception('金额最高可设置 100000');
            }

            if ($this->price) {
                $data['price'] = round($this->price, 2);
            }

            if ($this->is_clerk_enter) {
                $clerkUser= ClerkUser::find()->where([
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->user->identity->mch_id,
                    'is_delete' => 0,
                    'user_id' => \Yii::$app->user->id
                ])->one();

                $data['clerk_user_id'] = $clerkUser->user_id;
            }

            $commonQrCode = new CommonQrCode();
            $res = $commonQrCode->getQrCode($data, 240, 'plugins/scan_code/index/index');

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'qr_code' => $res
                ]
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}