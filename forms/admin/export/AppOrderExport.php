<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\admin\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\CoreFile;
use app\models\GoodsCats;
use app\models\ModelActiveRecord;
use yii\helpers\ArrayHelper;

class AppOrderExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'order_no',
                'value' => '订单号',
            ],
            [
                'key' => 'nickname',
                'value' => '用户',
            ],
            [
                'key' => 'app_name',
                'value' => '应用名称',
            ],
            [
                'key' => 'pay_price',
                'value' => '支付价格',
            ],
            [
                'key' => 'status',
                'value' => '状态',
            ],
        ];
    }

    /**
     * @param BaseActiveQuery $query
     */
    public function export($query = null)
    {
        $query = $this->query;
        $query = $query->orderBy('created_at');
        
        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '订单列表';
    }

    protected function transform($list)
    {
        $newList = [];
        /** @var GoodsCats $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['order_no'] = $item->order_no;
            $arr['nickname'] = $item->nickname;
            $arr['app_name'] = $item->app_name;
            $arr['pay_price'] = (float)$item->pay_price;
            $arr['status'] = $item->is_pay ? '已完成' : '待付款';
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    public function exportAction($query, $parmas = [])
    {
        \Yii::warning('导出开始');
        try {
            // 关闭日志存储
            ModelActiveRecord::$log = false;
            
            // 获取数据总数
            $query2 = clone $query;
            $count =$query2->count();

            $fieldsNameList = $this->getFields();

            $id = $this->getOnlyFolderId(); // 文件夹唯一标识
            $fileName = $this->getOnlyFileName($id); // 唯一文件名称

            $coreFile = new CoreFile();
            $coreFile->mall_id = 0;
            $coreFile->mch_id = 0;
            $coreFile->user_id = \Yii::$app->user->id;
            $coreFile->file_name = $fileName;

            $currentCount = 0;
            $isArray = isset($parmas['is_array']) ? $parmas['is_array'] : false;
            foreach ($query->asArray($isArray)->batch() as $item) {
                $this->transform($item);
                $dataList = $this->getDataList();
                (new CsvExport())->newAjaxExport($dataList, $fieldsNameList, $fileName, $id);

                $currentCount += count($item);
                $percent = price_format($currentCount / $count);
                $coreFile->percent = $percent;
                $res = $coreFile->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($coreFile));
                }
            }

            // 如果总数为空 则导出空表
            if ($count == 0) {
                (new CsvExport())->newAjaxExport([], $fieldsNameList, $fileName, $id);
            }

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

    // 文件夹唯一标识
    public function getOnlyFolderId()
    {
        return 'admin_' . \Yii::$app->user->id;
    }
}
