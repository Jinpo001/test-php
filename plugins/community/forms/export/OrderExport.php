<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 * Date: 2019/7/15
 * Time: 14:48
 */

namespace app\plugins\community\forms\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\CoreFile;

class OrderExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'middleman_name',
                'value' => '所属团长'
            ],
            [
                'key' => 'middleman_mobile',
                'value' => '手机号',
            ],
            [
                'key' => 'name',
                'value' => '买家昵称',
            ],
            [
                'key' => 'mobile',
                'value' => '买家手机号',
            ],
            [
                'key' => 'activity_name',
                'value' => '团购名称',
            ],
            [
                'key' => 'pay_price',
                'value' => '支付金额（元）',
            ],
            [
                'key' => 'profit_price',
                'value' => '团长利润（元）',
            ],
            [
                'key' => 'order_status',
                'value' => '订单状态',
            ],
            [
                'key' => 'created_at',
                'value' => '下单时间',
            ],
            [
                'key' => 'profit_status',
                'value' => '利润结算状态',
            ],
        ];
    }

    public function export($list = null)
    {
        \Yii::warning('导出开始');
        try {
            $list = $this->query;
            $this->getFields();
            // 文件夹唯一标识
            $id = \Yii::$app->mall->id . '_' . $this->mch_id;
            // 唯一文件名称
            $fileName = sprintf('%s%s%s%s', $this->getFileName(), $id, time(), '.csv');

            $coreFile = new CoreFile();
            $coreFile->mall_id = \Yii::$app->mall->id;
            $coreFile->mch_id = $this->mch_id;
            $coreFile->file_name = $fileName;

            $this->transform($list);
            $dataList = $this->getDataList();
            (new CsvExport())->newAjaxExport($dataList, $this->fieldsNameList, $fileName, $id);

            $coreFile->status = 1;
            $coreFile->percent = 1;
            $res = $coreFile->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($coreFile));
            }

            \Yii::warning('导出结束');
        }catch(\Exception $exception) {
            \Yii::error('导出异常');
            \Yii::error($exception);

            $coreFile->status = 2;
            $coreFile->save();
        }
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        return '团购订单列表';
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['order_no'] = $item['order_no'];
            $arr['middleman_name'] = $item['middleman_name'];
            $arr['middleman_mobile'] = $item['middleman_mobile'];
            $arr['name'] = $item['name'];
            $arr['mobile'] = $item['mobile'];
            $arr['activity_name'] = $item['activity_name'];
            $arr['pay_price'] = (float)$item['pay_price'];
            $arr['profit_price'] = (float)$item['profit_price'];
            $arr['created_at'] = $item['created_at'];
            switch ($item) {
                case $item['cancel_status'] == 1:
                    $arr['order_status'] = '已取消';
                    $arr['profit_status'] = '待结算';
                    break;
                case $item['is_sale'] == 1:
                    $arr['order_status'] = '已完成';
                    $arr['profit_status'] = '已结算';
                    break;
                case $item['is_confirm'] == 1:
                    $arr['order_status'] = '已收货';
                    $arr['profit_status'] = '待结算';
                    break;
                case $item['is_send'] == 1:
                    $arr['order_status'] = '待收货';
                    $arr['profit_status'] = '待结算';
                    break;
                case $item['is_pay'] == 1:
                    $arr['order_status'] = '待发货';
                    $arr['profit_status'] = '待结算';
                    break;
                default:
                    $arr['order_status'] = '未付款';
                    $arr['profit_status'] = '待结算';
            }

            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
