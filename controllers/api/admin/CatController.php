<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/5/29
 * Time: 13:47
 */

namespace app\controllers\api\admin;

use app\forms\PickLinkForm;
use app\forms\mall\cat\CatEditForm;
use app\forms\mall\cat\CatForm;

class CatController extends AdminController
{
    /**
     * 获取商品分类列表
     * @return \yii\web\Response
     */
    public function actionOptions()
    {
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getOptionList();

        return $this->asJson($res);
    }

    public function actionPickLink()
    {
        $model = new PickLinkForm();
        $res = $model->getLink();
        return $res;
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new CatEditForm();
            $form->attributes = json_decode(\Yii::$app->request->post('form'), true);
            $res = $form->save();

            return $this->asJson($res);
        } else {
            $form = new CatForm();
            $form->attributes = \Yii::$app->request->get();
            $detail = $form->getDetail();

            return $this->asJson($detail);
        }
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new CatForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->destroy();

        return $this->asJson($res);
    }
}