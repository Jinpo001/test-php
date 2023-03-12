<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\OrderRefund;
use app\plugins\teller\models\TellerOrders;


class TellerRefundForm extends Model
{
	public $refund_order_id;

    public function rules()
    {
        return [
            [['refund_order_id'], 'required'],
            [['refund_order_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'refund_order_id' => '退款订单ID',
        ];
    }

	public function refundDetail()
	{
		if (!$this->validate()) {
            return $this->getErrorResponse();
        }

		try {
            $orderRefund = OrderRefund::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->refund_order_id
            ])->with('detail', 'order.paymentOrder')->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            $goodsInfo = json_decode($orderRefund->detail->goods_info, true);

            $attrList = [];
            foreach ($goodsInfo['attr_list'] as $item) {
                $attrItem = [];
                $attrItem = sprintf('%s:%s', $item['attr_group_name'], $item['attr_name']);
                $attrList[] = $attrItem;
            }


            $data = [
                'id' => $orderRefund->id,
                'goods_name' => $goodsInfo['goods_attr']['name'],
                'goods_cover_pic' => $goodsInfo['goods_attr']['pic_url'],
                'attr_list' => $attrList,
                'num' => $orderRefund->detail->num,
                'total_price' => $orderRefund->detail->total_price,
                'refund_type' => $orderRefund->type == 1 ? '退货退款' : '仅退款',
                'refund_way' => (new TellerOrders())->getPayWay($orderRefund->order->paymentOrder->pay_type),
                'remark' => $orderRefund->remark,
                'refund_price' => $orderRefund->reality_refund_price,
            ];

			return [
				'code' => ApiCode::CODE_SUCCESS,
				'msg' => '请求成功',
                'data' => $data
			];
		}catch(\Exception $exception) {
            return [
                'code'  => ApiCode::CODE_ERROR,
                'msg'   => $exception->getMessage(),
                'line' => $exception->getLine(),
            ];
		}
	}
}
