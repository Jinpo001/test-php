<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\pintuan\controllers\mall;

use app\forms\mall\order\OrderDestroyForm;
use app\forms\mall\order\OrderDetailForm;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\Controller;
use app\plugins\pintuan\forms\common\v2\PintuanSuccessForm;
use app\plugins\pintuan\forms\mall\OrderCancelForm;
use app\plugins\pintuan\forms\mall\OrderForm;
use app\plugins\pintuan\jobs\v2\PintuanCreatedOrderJob;
use app\plugins\pintuan\models\PintuanGoods;
use app\plugins\pintuan\models\PintuanOrderRelation;
use app\plugins\pintuan\models\PintuanOrders;
use yii\helpers\ArrayHelper;

class OrderController extends Controller
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderForm();
            $form->attributes = \Yii::$app->request->get();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new OrderForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new OrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            $res = $form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->search();
            $order = $res['data']['order'];
            if ($order['orderRelation']['pintuanOrder']['status'] == 1 ||
                $order['orderRelation']['pintuanOrder']['status'] == 3) {
                $order['is_send_show'] = 0;
                $order['is_cancel_show'] = 0;
                $order['is_clerk_show'] = 0;
            }
            $res['data']['order'] = $order;
            return $this->asJson($res);
        } else {
            return $this->render('detail');
        }
    }

    //清空回收站
    public function actionDestroyAll()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderDestroyForm();
            return $this->asJson($form->setSign(\Yii::$app->plugin->getCurrentPlugin()->getName())->destroyAll());
        }
    }

    public function actionOrderCancel()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderCancelForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        }
    }

    public function actionCreateJob()
    {
        $orderId = [1,2,3];
        $pintuanOrders = PintuanOrders::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $orderId,
            'status' => 1
        ])->all();

        $count = 0;
        foreach ($pintuanOrders as $pintuanOrder) {
            $count += 1;
            \Yii::$app->queue->delay(0)
                ->push(new PintuanCreatedOrderJob([
                    'pintuan_order_id' => $pintuanOrder->id,
            ]));
        }

        return '成功执行' . $count . '条';
    }
}
