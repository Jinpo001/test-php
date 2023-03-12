<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2018/10/30 14:44
 */


namespace app\plugins\url_scheme\controllers\mall;

use app\plugins\Controller;
use app\plugins\url_scheme\forms\EditForm;
use app\plugins\url_scheme\forms\ListForm;
use app\plugins\url_scheme\forms\OperateForm;

class IndexController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            } else {
                $form = new EditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('index');
        }
    }

    public function actionOperate()
    {
        $form = new OperateForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->execute());
    }
}
