<?php

namespace app\forms\common\order\send\job;

use app\models\CityPreviewOrder;
use yii\base\Component;
use yii\queue\JobInterface;

class MtCityServiceJob extends Component implements JobInterface
{    
    public $mock_type;
    public $instance;
    public $preview_order_id;

    public function execute($queue)
    {
        try {
            \Yii::warning('美团模拟配送开始ID');
            $preOrder = CityPreviewOrder::findOne($this->preview_order_id);

            if (!$preOrder) {
                throw new \Exception('预下单数据不存在');
            }

            $resultData = json_decode($preOrder->result_data, true);
            
            $instance = $this->instance;

            if (in_array($this->mock_type, ['arrange', 'pickup', 'deliver'])) {
                $result = $instance->mockUpdateOrder(
                    [
                        'delivery_id' => $resultData['delivery_id'],
                        'mt_peisong_id' => $resultData['mt_peisong_id'],
                    ],
                    [
                        'mock_type' => $this->mock_type,
                    ]
                );
                \Yii::warning($result);
                \Yii::warning('美团模拟配送是否成功' . $result->isSuccessful());
            }

            if ($this->mock_type == 'cancelOrder') {
                $result = $instance->cancelOrder(
                    [
                        'delivery_id' => $resultData['delivery_id'],
                        'mt_peisong_id' => $resultData['mt_peisong_id'],
                        'cancel_reason_id' => 101, // 101.顾客主动取消
                        'cancel_reason' => '客户取消',
                    ]
                );
                \Yii::warning($result);
                \Yii::warning('美团模拟订单取消是否成功' . $result->isSuccessful());
            }
        } catch (\Exception $e) {
            \Yii::error('美团模拟配送异常');
            \Yii::error($e);
        }
    }
}
