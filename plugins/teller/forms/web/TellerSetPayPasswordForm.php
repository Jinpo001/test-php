<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\forms\api\mall_member\SetPayPasswordForm;
use app\models\User;

class TellerSetPayPasswordForm extends SetPayPasswordForm
{
    public $user_id;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'user_id' => '会员ID'
        ]);
    }

    public function getUser()
    {
        $user = User::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->user_id,
            'is_delete' => 0
        ])
            ->with('userInfo')
            ->one();

        return $user;
    }
}
