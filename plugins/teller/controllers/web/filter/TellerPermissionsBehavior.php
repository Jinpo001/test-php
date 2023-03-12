<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2019 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\controllers\web\filter;

use Yii;
use app\forms\common\CommonAuth;
use app\forms\common\CommonUser;
use app\plugins\teller\models\TellerCashier;
use yii\base\ActionFilter;

class TellerPermissionsBehavior extends ActionFilter
{
    /**
     * 安全路由，权限验证时会排除这些路由
     * @var array
     */
    private $safeRoute = [];

    public function beforeAction($action)
    {
        if (\Yii::$app->user->isGuest == false) {
            //路由名称
            $route = Yii::$app->requestedRoute;
            //排除安全路由
            if (in_array($route, $this->safeRoute)) {
                return true;
            }

            // TODO 异步请求不验证
            if (Yii::$app->request->isAjax) {
                return true;
            }

            $cashier = TellerCashier::findOne(['user_id' => \Yii::$app->user->id]);
            
            if (!$cashier) {
                $this->permissionError();
            }
        }

        return true;
    }

    public function permissionError()
    {
        $response = Yii::$app->getResponse();
        $response->data = Yii::$app->controller->renderFile('@app/views/error/error.php');
        $response->send();
    }
}
