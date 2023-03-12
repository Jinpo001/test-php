<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/20
 * Time: 3:47 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\forms\common\platform\PlatformConfig;
use app\models\Order;
use app\plugins\minishop\models\MinishopOrder;
use app\plugins\wxapp\models\shop\ShopFactory;

class OrderForm extends Model
{
    /**
     * @var Order $order
     */
    public $order;
    public $type;
    public $action_type;

    public function execute()
    {
        try {
            if ($this->order->is_pay != 1 || !$this->order->paymentOrder) {
                \Yii::warning('订单未支付时，不需要提交到自定版交易组件上');
                return true;
            }
            $minihopOrder = MinishopOrder::findOne([
                'payment_order_union_id' => $this->order->paymentOrder->payment_order_union_id,
                'mall_id' => \Yii::$app->mall->id,
            ]);
            if (!$minihopOrder) {
                \Yii::warning('未在交易组件中创建订单，不进行后续订单操作' . $this->order->paymentOrder->payment_order_union_id);
                return true;
            }
            $platformList = PlatformConfig::getInstance()->getPlatformOpenid($this->order->user);

            if (!isset($platformList['wxapp'])) {
                \Yii::warning('获取不到微信小程序用户openid不上传到小商店');
                return true;
            }
            $openid = $platformList['wxapp'];
            $form = new CheckForm();
            $plugin = $form->check();
            $shopService = $plugin->getShopService();
            switch ($this->type) {
                case 'pay':
                    if ($minihopOrder->status != 10) {
                        \Yii::warning('订单状态已修改');
                        return true;
                    }
                    $shopService->order->pay([
                        'out_order_id' => $this->order->paymentOrder->payment_order_union_id,
                        'openid' => $openid,
                        'action_type' => 1,
                        'action_remark' => '',
                        'transaction_id' => $this->order->paymentOrder->payment_order_union_id,
                        'pay_time' => $this->order->pay_time
                    ]);
                    break;
                case 'cancel':
                    if ($minihopOrder->status != 10) {
                        \Yii::warning('订单状态已修改');
                        return true;
                    }
                    $shopService->order->pay([
                        'out_order_id' => $this->order->paymentOrder->payment_order_union_id,
                        'openid' => $openid,
                        'action_type' => $this->action_type,
                        'action_remark' => '',
                    ]);
                    $minihopOrder->status = 20;
                    break;
                case 'send':
                    $deliveryList = [];
                    if ($this->order->send_type == 0) {
                        $list = $this->getCompanyList($shopService);
                        foreach ($this->order->detailExpress as $detailExpress) {
                            if ($detailExpress->send_type == 2 || !isset($list[$detailExpress->express])) {
                                $deliveryList[] = [
                                    'delivery_id' => 'OTHERS',
                                    'waybill_id' => 'OTHERS'
                                ];
                            } else {
                                $deliveryList[] = [
                                    'delivery_id' => $list[$detailExpress->express],
                                    'waybill_id' => $detailExpress->express_no
                                ];
                            }
                        }
                    }
                    $shopService->delivery->send([
                        'out_order_id' => $this->order->paymentOrder->payment_order_union_id,
                        'openid' => $openid,
                        'finish_all_delivery' => $this->order->is_send == 1 ? 1 : 0,
                        'delivery_list' => $deliveryList
                    ]);
                    if ($this->order->is_send == 1) {
                        $minihopOrder->status = 30;
                    }
                    break;
                case 'confirm':
                    $shopService->delivery->receive([
                        'out_order_id' => $this->order->paymentOrder->payment_order_union_id,
                        'openid' => $openid,
                    ]);
                    $minihopOrder->status = 100;
                    break;
                default:
            }
            if (!$minihopOrder->save()) {
                throw new \Exception($this->getErrorMsg($minihopOrder));
            }
        } catch (\Exception $exception) {
            \Yii::warning($exception);
        }
        return true;
    }

    /**
     * @param ShopFactory $shopService
     */
    protected function getCompanyList($shopService)
    {
        if ($list = \Yii::$app->cache->get('minishop_delivery_list')) {
            return $list;
        }
        $res = $shopService->delivery->getCompanyList();
        $list = array_column($res['company_list'], 'delivery_id', 'delivery_name');
        \Yii::$app->cache->set('minishop_delivery_list', $list, 86400);
        return $list;
    }
}
