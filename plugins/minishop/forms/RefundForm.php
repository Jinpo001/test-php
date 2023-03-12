<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/22
 * Time: 2:15 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\forms\common\platform\PlatformConfig;
use app\models\OrderRefund;
use app\plugins\minishop\models\MinishopOrder;
use app\plugins\minishop\models\MinishopRefund;
use yii\helpers\Html;
use yii\helpers\Json;

class RefundForm extends Model
{
    /**
     * @var OrderRefund $refund
     */
    public $refund;

    public $type;

    public function execute()
    {
        $minihopOrder = MinishopOrder::findOne([
            'payment_order_union_id' => $this->refund->order->paymentOrder->payment_order_union_id,
            'mall_id' => \Yii::$app->mall->id,
        ]);
        if (!$minihopOrder) {
            \Yii::warning('未在交易组件中创建订单，不进行后续订单操作' . $this->refund->order->paymentOrder->payment_order_union_id);
            return true;
        }
        try {
            $platformList = PlatformConfig::getInstance()->getPlatformOpenid($this->refund->user);

            if (!isset($platformList['wxapp'])) {
                \Yii::warning('获取不到微信小程序用户openid不上传到小商店');
                return true;
            }
            $openid = $platformList['wxapp'];
            $form = new CheckForm();
            $plugin = $form->check();
            $shopService = $plugin->getShopService();
            $params = [
                'path' => '/pages/order/refund/index'
            ];
            switch ($this->type) {
                case 'create':
                    $productInfos = [];
                    $data = Json::decode($minihopOrder->data, true);
                    foreach ($data['order_detail']['product_infos'] as $value) {
                        if ($value['out_detail_id'] == $this->refund->order_detail_id) {
                            $productInfos[] = [
                                'out_product_id' => $value['out_product_id'],
                                'out_sku_id' => $value['out_sku_id'],
                                'product_cnt' => $value['product_cnt']
                            ];
                        }
                    }
                    if ($this->refund->type == 1) {
                        $type = 2;
                    } elseif ($this->refund->type == 2) {
                        $type = 3;
                    } else {
                        $type = 1;
                    }
                    $args = [
                        'out_order_id' => $minihopOrder->payment_order_union_id,
                        'out_aftersale_id' => $this->refund->id,
                        'openid' => $openid,
                        'type' => $type,
                        'create_time' => $this->refund->created_at,
                        'status' => 0,
                        'finish_all_aftersale' => 0,
                        'path' => Html::encode('/pages/index/index?scene=share&param=') . Json::encode($params, JSON_UNESCAPED_UNICODE),
                        'product_infos' => $productInfos
                    ];
                    $shopService->sale->add($args);
                    $model = new MinishopRefund();
                    $model->mall_id = \Yii::$app->mall->id;
                    $model->order_id = $this->refund->order_id;
                    $model->order_refund_id = $this->refund->id;
                    $model->status = $args['status'];
                    $model->aftersale_infos = Json::encode($args, JSON_UNESCAPED_UNICODE);
                    if (!$model->save()) {
                        throw new \Exception($this->getErrorMsg($model));
                    }
                    break;
                case 'update':
                    $args = [
                        'out_order_id' => $minihopOrder->payment_order_union_id,
                        'out_aftersale_id' => $this->refund->id,
                        'status' => 0,
                        'finish_all_aftersale' => 0,
                    ];
                    // 用户取消
                    if ($this->refund->is_delete == 1) {
                        $args['status'] = 1;
                    }
                    if ($this->refund->status == 3) {
                        // 商家拒绝售后申请
                        $args['status'] = $this->refund->type == 3 ? 4 : 5;
                    } else {
                        // 商家同意售后申请
                        if ($this->refund->type == 3) {
                            //  仅退款 商家退款中
                            $args['status'] = 11;
                        } else {
                            $args['status'] = 6;
                            if ($this->refund->is_send == 1) {
                                $args['status'] = 8;
                            }
                            if ($this->refund->is_confirm == 1) {
                                $args['status'] = 11;
                            }
                        }
                        if ($this->refund->is_refund == 1) {
                            $args['status'] = $this->refund->type == 3 ? 13 : 14;
                        }
                    }
                    $shopService->sale->update($args);
                    $model = MinishopRefund::findOne([
                        'mall_id' => \Yii::$app->mall->id, 'order_refund_id' => $this->refund->id,
                        'order_id' => $this->refund->order_id
                    ]);
                    if ($model) {
                        $model->status = $args['status'];
                        if (!$model->save()) {
                            throw new \Exception($this->getErrorMsg($model));
                        }
                    }
                    break;
                default:
            }
        } catch (\Exception $exception) {
            \Yii::warning($exception);
        }
        return true;
    }
}
