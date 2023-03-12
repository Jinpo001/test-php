<?php


namespace app\jobs;


use app\core\response\ApiCode;
use app\forms\api\order\OrderAppraiseForm;
use app\models\Mall;
use app\models\OrderDetail;
use yii\queue\JobInterface;

class OrderAutoComments extends BaseJob implements JobInterface
{
    public $orderDetail;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        try {
            $this->doSomeThing();
        } catch (\Exception $e) {
            \Yii::error('默认好评ERROR');
            \Yii::error($e->getMessage());
        }
    }

    private function doSomeThing()
    {
        $mall = Mall::findOne(['id' => $this->orderDetail->order->mall_id]);
        \Yii::$app->setMall($mall);

        $form = new OrderAppraiseForm();
        $form->order_id = $this->orderDetail->order_id;
        $form->appraiseData = json_encode([[
            'id' => $this->orderDetail->id,
            'grade_level' => 5,
            'content' => '买家未及时作出评价，系统默认好评！',
            'pic_list' => [],
            'is_anonymous' => 1,
        ]]);
        $return = $form->appraise();
        if ($return['code'] !== ApiCode::CODE_SUCCESS) {
            throw new \Exception($return['msg']);
        }
    }
}