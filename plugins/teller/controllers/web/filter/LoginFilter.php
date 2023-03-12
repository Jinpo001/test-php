<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/3/21
 * Time: 13:56
 */

namespace app\plugins\teller\controllers\web\filter;


use app\core\response\ApiCode;
use app\models\Mall;
use yii\base\ActionFilter;

class LoginFilter extends ActionFilter
{
    public $safeRoutes;

    public function beforeAction($action)
    {
        if (is_array($this->safeRoutes) && in_array(\Yii::$app->requestedRoute, $this->safeRoutes)) {
            return parent::beforeAction($action);
        }

        if (!\Yii::$app->user->isGuest) {
            \Yii::$app->setSessionMallId(\Yii::$app->user->identity->mall_id);
            \Yii::$app->setMall(Mall::findOne(\Yii::$app->user->identity->mall_id));
            return parent::beforeAction($action);
        }

        $mallId = isset($_COOKIE['__mall_id']) ? $_COOKIE['__mall_id'] : 0;

        $loginUrl = \Yii::$app->urlManager->createAbsoluteUrl(['plugin/teller/web/passport/login', $mallId]);
        \Yii::$app->response->redirect($loginUrl);

        return false;
    }
}
