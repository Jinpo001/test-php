<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/12/12
 * Time: 4:05 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\handlers;


use app\events\UserEvent;
use app\forms\common\share\CommonShare;

class UserLoginHandler extends HandlerBase
{

    public function register()
    {
        \Yii::$app->on(\app\models\User::EVENT_LOGIN, function ($event) {
            /* @var UserEvent $event*/
            $this->bindParent($event->user);
            $this->becomeShare($event->user);
        });
    }

    /**
     * 无条件成为分销商
     */
    protected function becomeShare($user)
    {
        try {
            if (!$user) {
                \Yii::warning('用户不存在');
                return false;
            }
            $commonShare = new CommonShare();
            $commonShare->mall = \Yii::$app->mall;
            $commonShare->becomeShareByNone($user);
            return true;
        } catch (\Exception $exception) {
            \Yii::error('无条件成为分销商: ' . $exception->getMessage());
            return false;
        }
    }

    private function bindParent($user)
    {
        $headers = \Yii::$app->request->headers;
        $userId = empty($headers['x-user-id']) ? null : $headers['x-user-id'];
        if (!$userId) {
            return $this;
        }
        $common = CommonShare::getCommon();
        $common->mall = \Yii::$app->mall;
        $common->user = $user;
        try {
            $common->bindParent($userId, 1);
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
            $userInfo = $common->user->userInfo;
            $userInfo->temp_parent_id = $userId;
            $userInfo->save();
        }
        return true;
    }
}