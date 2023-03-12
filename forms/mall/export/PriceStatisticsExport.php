<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\models\CoreFile;

class PriceStatisticsExport extends BaseExport
{

    public $start_time;
    public $end_time;
    public $platform;

    public function fieldsList()
    {
        return [
            // [
            //     'key' => 'platform',
            //     'value' => '所属平台',
            // ],
            [
                'key' => 'order_price',
                'value' => '订单收益（元）',
            ],
            [
                'key' => 'member_price',
                'value' => '会员购买收益（元）',
            ],
            [
                'key' => 'balance',
                'value' => '余额充值收益（元）',
            ],
            [
                'key' => 'cash_price',
                'value' => '提现支出（元）',
            ],
            [
                'key' => 'income_price',
                'value' => '实际收益（元）',
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

    public function getFileName()
    {
        return '对账单';
    }

    protected function transform($item)
    {
        $newList = [];
        $arr = [];
        $item['date'] = empty($this->start_time) ? '全部' : $this->start_time . '-' . $this->end_time;
        $item['order_price'] = floatval($item['order_price']);
        $item['member_price'] = floatval($item['member_price']);
        $item['balance'] = floatval($item['balance']);
        $item['cash_price'] = floatval(0 - $item['cash_price']);
        $item['income_price'] = floatval($item['income_price']);
        // $item['platform'] = $item['platform'] == '未知' ? '全部' : $this->getPlatform($this->platform);

        $arr = array_merge($arr, $item);
        $newList[] = $arr;
        $this->dataList = $newList;
    }

    protected function getFields()
    {
        $arr = [];
        foreach ($this->fieldsList() as $key => $item) {
            $arr[$key] = $item['key'];
        }
        $this->fieldsKeyList = $arr;
        $fieldsList = $this->fieldsList();
        $newFields = ['日期'];
        if ($this->fieldsKeyList) {
            foreach ($this->fieldsKeyList as $field) {
                foreach ($fieldsList as $item) {
                    if ($item['key'] === $field) {
                        $newFields[] = $item['value'];
                    }
                }
            }
        }
        $this->fieldsKeyList = array_merge(['date'], $this->fieldsKeyList ?: []);
        $this->fieldsNameList = $newFields;
    }
}
