<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */


namespace app\controllers\api;


use app\controllers\api\ApiController;
use app\forms\api\video_number\VideoNumberEditForm;

class VideoNumberController extends ApiController
{
    // 添加素材
    public function actionAddMaterial()
    {
        $form = new VideoNumberEditForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->save();

        return $this->asJson($res);
    }

    public function actionGetArticleUrl()
    {
    	$form = new VideoNumberEditForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getArticleUrl();

        return $this->asJson($res);
    }
}
