<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\common\order\rprint;



use app\models\Order;
use app\models\OrderExpressSingle;

class BirdPrint extends BaseForm
{
    public function track(...$params)
    {
        $params = func_get_arg(0);
        list($delivery, $express, $order) = $this->getPrintAttributes();

        //构造电子面单提交信息
        $eorder = [];
        $eorder["PayType"] = 1;
        $delivery['customer_account'] && $eorder['PayType'] = 3;
        $delivery['customer_account'] && $eorder['CustomerName'] = $delivery['customer_account'];
        $delivery['customer_pwd'] && $eorder['CustomerPwd'] = $delivery['customer_pwd'];
        $delivery['outlets_code'] && $eorder['SendSite'] = $delivery['outlets_code'];
        $delivery['month_code'] && $eorder['MonthCode'] = $delivery['month_code'];

        $eorder['TemplateSize'] = $delivery['template_size'];
        $eorder['IsSendMessage'] = $delivery['is_sms'];
        $eorder["ShipperCode"] = $express['code'];
        $eorder['ExpType'] = $delivery['business_type'];
        $eorder["ShipperCode"] === 'JD' && $eorder["ExpType"] = 6;
        $eorder["IsReturnPrintTemplate"] = 1;
        $eorder["Quantity"] = 1;
        if (empty($order->detailExpress) && array_column($order->detail, 'id') == $params['order_detail_ids']) {
            $eorder["OrderCode"] = $order->order_no;
        } else if (empty($order->detailExpress)) {
            $eorder["OrderCode"] = $order->order_no . "0" . mt_rand(0, 10000);
        } else {
            $eorder["OrderCode"] = $order->order_no . count($order->detailExpress) . mt_rand(0, 10000);
        }
        $eorder["Sender"] = $this->selectSender($delivery);
        $eorder["Receiver"] = $this->selectReceiver($order, $params['zip_code'] ?? '');
        $eorder["Commodity"] = $this->selectCommodity($delivery, $order, $params['order_detail_ids'], $customArea);
        $eorder["CustomArea"] = $customArea;
        //调用电子面单
        $jsonParam = \Yii::$app->serializer->encode($eorder);
        $jsonResult = \Yii::$app->kdOrder->submitEOrder($jsonParam);

        //解析电子面单返回结果
        $result = \yii\helpers\BaseJson::decode($jsonResult);
        if (isset($result["ResultCode"]) && ($result["ResultCode"] == "100" || $result["ResultCode"] == '106')) {
            while (current($params['order_detail_ids']) !== false) {
                $form = OrderExpressSingle::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'ebusiness_id' => $result['EBusinessID'],
                    'order_id' => $order->id,
                    'express_code' => $express['code'],
                    'order_detail_id' => current($params['order_detail_ids'])
                ]);
                if (empty($form)) {
                    $form = new OrderExpressSingle();
                    $form->mall_id = \Yii::$app->mall->id;
                    //$form->ebusiness_id = $result['taskId'];
                    $form->ebusiness_id = $result['EBusinessID'];
                    $form->order_id = $order->id;
                    $form->express_code = $express['code'];
                    $form->order_detail_id = current($params['order_detail_ids']);
                }
                $form->order = \Yii::$app->serializer->encode($result['Order']);
                $form->print_teplate = empty($result['PrintTemplate']) ? '' : $result['PrintTemplate'];
                $form->is_delete = 0;
                if (!$form->save()) {
                    throw new \Exception($this->getErrorMsg($form));
                }
                next($params['order_detail_ids']);
            }
            return array_merge($result, ['express_single' => $form]);
        } else {
            throw new \DomainException(\yii\helpers\BaseJson::encode($result));
        }
    }

    private function selectCommodity(array $delivery, Order $order, $order_detail_ids, &$customArea): array
    {
        $commodity = [];
        $customArea = '';
        if (true || $delivery['is_goods']) {
            foreach ($order->detail as $v) {
                if (!empty($order_detail_ids) && !in_array($v['id'], $order_detail_ids)) {
                    //排除订单
                    continue;
                }
                $commodityOne = [];
                $commodityOne["GoodsName"] = $this->getGoodsName($v, $delivery, $goods_attr);
                $customArea .= $this->getGoodsName($v, $delivery, $goods_attr, 500). "<br>";;
                $commodityOne["GoodsCode"] = "";
                $commodityOne["Goodsquantity"] = (int)$v->num;
                $commodityOne["GoodsPrice"] = $goods_attr['price'];
                $commodityOne["GoodsWeight"] = floor($goods_attr['weight']) / 1000;
                $commodityOne['GoodsDesc'] = "";
                $commodityOne['GoodsVol'] = "";
                $commodity[] = $commodityOne;
            }
        } else {
            $commodityOne = [];
            $commodityOne["GoodsName"] = '商品';
            $commodityOne["GoodsCode"] = "";
            $commodityOne["Goodsquantity"] = "";
            $commodityOne["GoodsPrice"] = "";
            $commodityOne["GoodsWeight"] = "";
            $commodityOne['GoodsDesc'] = "";
            $commodityOne['GoodsVol'] = "";
            $commodity[] = $commodityOne;
        }
        return $commodity;
    }

    private function selectSender(array $delivery): array
    {
        return [
            'Company' => $delivery['company'],
            'Name' => $delivery['name'],
            'Tel' => $delivery['tel'],
            'Mobile' => $delivery['mobile'],
            'PostCode' => $delivery['zip_code'],
            'ProvinceName' => $delivery['province'],
            'CityName' => $delivery['city'],
            'ExpAreaName' => $delivery['district'],
            'Address' => $delivery['address'],
        ];
    }


    private function selectReceiver(Order $order, $zip_code): array
    {
        $address_data = explode(' ', $order->address, 4);
        return [
            //'Company' => '',
            //'Tel' => '',
            'Name' => $order->name,
            'Mobile' => $order->mobile,
            'PostCode' => $zip_code,
            'ProvinceName' => $address_data[0] ?: '空',
            'CityName' => $address_data[1] ?: '空',
            'ExpAreaName' => $address_data[2] ?: '空',
            'Address' => str_replace(PHP_EOL, '', $address_data[3] ?: $order->address),
        ];
    }
}