<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/9/29
 * Time: 4:13 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\controllers\mall;

use app\plugins\Controller;
use app\plugins\wechat\forms\mall\IndexForm;
use app\plugins\wechat\forms\mall\OtherForm;
use app\plugins\wechat\forms\mall\WechatConfigForm;

class ConfigController extends Controller
{

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new IndexForm();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('index');
        }
    }

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new WechatConfigForm();
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            }
            if (\Yii::$app->request->isGet) {
                $form = new WechatConfigForm();
                return $form->getDetail();
            }
        } else {
            return $this->render('setting');
        }
    }

    public function actionOther()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OtherForm();
            if (\Yii::$app->request->isGet) {
                return $this->asJson($form->getOther());
            } else {
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        } else {
            return $this->render('other');
        }
    }

    public function actionIssue()
    {
        $form = new IndexForm();
        return $this->asJson($form->issue());
    }
}
