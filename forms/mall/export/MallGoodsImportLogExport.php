<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\Pagination;
use app\core\response\ApiCode;
use app\forms\mall\export\CsvExport;
use app\forms\mall\export\MallGoodsExport;
use app\forms\mall\goods\ImportDataLogForm;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\CoreFile;
use app\models\Goods;
use yii\helpers\ArrayHelper;

class MallGoodsImportLogExport extends MallGoodsExport
{
    public $list; // 错误数据
    public $error_list; // 错误日志

    /**
     * @param BaseActiveQuery $query
     * @return array|bool|resource
     */
    public function export($query = null)
    {
        $fieldsKeyList = [];
        foreach ($this->fieldsList() as $item) {
            $fieldsKeyList[] = $item['key'];
        }
        $this->fieldsKeyList = $fieldsKeyList;


        \Yii::warning('导出开始');
        try {
            $fieldsNameList = $this->getFields();
            // 文件夹唯一标识
            $id = \Yii::$app->mall->id . '_' . $this->mch_id;
            // 临时 文件夹唯一标识
            $temporaryId = sprintf('%s_%s%s%s', \Yii::$app->mall->id, $this->mch_id, '/goods_import_', time());
            // 唯一文件名称
            $zipFileName = sprintf('%s%s%s%s', $this->getFileName(), $id, time(), '.zip');

            $coreFile = new CoreFile();
            $coreFile->mall_id = \Yii::$app->mall->id;
            $coreFile->mch_id = $this->mch_id;
            $coreFile->file_name = $zipFileName;


            $temporaryFileNameList = [];
            // 临时 唯一文件名称
            $temporaryFileName = sprintf('%s%s%s', $this->getFileName(), time(), '.csv');
            $temporaryFileNameList[] = $temporaryFileName;
            (new CsvExport())->newAjaxExport($this->list, $fieldsNameList, $temporaryFileName, $temporaryId);


            // 临时 唯一文件名称
            $temporaryFileName = sprintf('%s%s%s', '错误日志', time() + 86400, '.csv');
            $temporaryFileNameList[] = $temporaryFileName;
            // 错误日志
            $fieldsNameList = ['商品名称', '错误信息'];
            (new CsvExport())->newAjaxExport($this->error_list, $fieldsNameList, $temporaryFileName, $temporaryId);


            // 获取临时文件目录路径
            $zipFilePath = sprintf('%s%s%s%s', \Yii::$app->basePath, '/web/csv/', $id, '/');
            $dirPath = \Yii::$app->basePath . '/web/csv/' . $temporaryId;

            // 生成压缩包
            $zip = new \ZipArchive();
            $zip->open($zipFilePath . $zipFileName, \ZipArchive::CREATE);
            foreach ($temporaryFileNameList as $temporaryFileName) {
                $newFileName = $dirPath . '/' . $temporaryFileName;
                $zip->addFile($newFileName,basename($newFileName));                    
            }
            $zip->close();
            
            $this->deleteDir($dirPath);

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
        return '商品导入异常数据';
    }

    protected function transform($list)
    {
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['name'] = $item['name'];
            $arr['original_price'] = $item['original_price'];
            $arr['cost_price'] = $item['cost_price'];
            $arr['detail'] = $item['detail'];
            $arr['cover_pic'] = $item['cover_pic'];
            $arr['pic_url'] = $item['pic_url'];
            $arr['video_url'] = $item['video_url'];
            $arr['unit'] = $item['unit'];
            $arr['price'] = $item['price'];
            $arr['use_attr'] = $item['use_attr'];
            $arr['attr_groups'] = $item['attrGroups'];
            $arr['goods_stock'] = 0;
            $arr['virtual_sales'] = $item['virtual_sales'];
            $arr['confine_count'] = $item['confine_count'];
            $arr['pieces'] = $item['pieces'];
            $arr['forehead'] = $item['forehead'];
            $arr['give_integral'] = $item['give_integral'];
            $arr['give_integral_type'] = $item['give_integral_type'];
            $arr['forehead_integral'] = $item['forehead_integral'];
            $arr['forehead_integral_type'] = $item['forehead_integral_type'];
            $arr['accumulative'] = $item['accumulative'];
            $arr['sign'] = $item['sign'];
            $arr['app_share_pic'] = $item['app_share_pic'];
            $arr['app_share_title'] = $item['app_share_title'];
            $arr['sort'] = $item['sort'];
            $arr['confine_order_count'] = $item['confine_order_count'];
            $arr['is_area_limit'] = $item['is_area_limit'];
            $arr['area_limit'] = $item['area_limit'];
            $arr['shipping_id'] = $item['shipping_id'];
            $arr['attr'] = $item['attr'];
            $arr['is_quick_shop'] = $item['is_quick_shop'];
            $arr['is_sell_well'] = $item['is_sell_well'];
            $arr['is_negotiable'] = $item['is_negotiable'];
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
