<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\pond\controllers\mall;

use app\plugins\Controller;
use app\plugins\pond\forms\mall\PondSettingForm;

class SettingController extends Controller
{
    public function actionBatchGoods()
    {
        $p = \Yii::$app->request->get('p');
        if ($p === 'a7dd12b1dab17d25467b0b0a4c8d4a92') {
            //查看脏数据
            $goods = \app\models\Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'sign' => 'pond',
                'is_delete' => 0,
                'status' => 0
            ])->asArray()->all();
            dd($goods);
        }
        if ($p === '335cf4508dd597be4bfc9caa3e08b901') {
            //修改脏数据
            $goods = \app\models\Goods::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'sign' => 'pond',
                'is_delete' => 0,
                'status' => 0
            ])->all();
            foreach ($goods as $item) {
                $item->status = 1;
                $item->save();
            }
            dd('修改成功');
        }
        dd('不合法');
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new PondSettingForm();
            if (\Yii::$app->request->isPost) {
                $form->attributes = \Yii::$app->request->post();
                return $form->save();
            } else {
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }
}
