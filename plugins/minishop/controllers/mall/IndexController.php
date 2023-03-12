<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/9
 * Time: 5:12 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\controllers\mall;

use app\plugins\minishop\filter\WxappFilter;
use app\plugins\minishop\forms\GoodsForm;
use app\plugins\minishop\forms\IndexForm;
use app\plugins\minishop\forms\OperateForm;
use app\plugins\minishop\forms\RefreshForm;
use app\plugins\minishop\forms\UpdateForm;

class IndexController extends MallController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'wxappFilter' => [
                'class' => WxappFilter::class,
            ],
        ]);
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new IndexForm();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('index');
        }
    }

    public function actionCat()
    {
        $form = new IndexForm();
        return $this->asJson($form->getCat());
    }

    public function actionGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    public function actionOperate()
    {
        $form = new OperateForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->execute());
    }

    public function actionRefresh()
    {
        $form = new RefreshForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->refresh());
    }

    public function actionUpdate()
    {
        $form = new UpdateForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }
}
