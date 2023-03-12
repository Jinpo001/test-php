<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\exchange\core;

use app\forms\api\LoginForm;
use app\forms\api\LoginUserInfo;
use app\models\User;

//类调整
class CUser extends LoginForm
{
    public const key = 'GiftCardLoginUserOnly';
    protected function getUserInfo()
    {
        $userInfo = new LoginUserInfo();
        $userInfo->username = self::key;
        $userInfo->nickname = '匿名用户';
        $userInfo->avatar = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/plugins/user-default-avatar.png';
        $userInfo->platform_user_id = '';
        $userInfo->platform = '';
        return $userInfo;
    }

    public function getUser()
    {
        $result = $this->login();
        return User::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'access_token' => $result['data']['access_token'],
            'is_delete' => 0,
        ])->one();
    }

    public function updateUser()
    {
    }
}
