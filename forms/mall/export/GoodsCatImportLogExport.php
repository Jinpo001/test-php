<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\forms\mall\export\GoodsCatExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\CoreFile;
use app\models\GoodsCats;
use yii\helpers\ArrayHelper;

class GoodsCatImportLogExport extends GoodsCatExport
{
    public $list; // 错误数据
    public $error_list; // 错误日志

    /**
     * @param BaseActiveQuery $query
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
            $temporaryId = sprintf('%s_%s%s%s', \Yii::$app->mall->id, $this->mch_id, '/cat_import_', time());
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
            $fieldsNameList = ['分类名称', '错误信息'];
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

    public function getFileName()
    {
        return '分类导入异常数据';
    }

    protected function transform($list)
    {
        $newList = [];
        /** @var GoodsCats $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['name'] = $item['name'];
            $arr['pic_url'] = $item['pic_url'];
            $arr['sort'] = $item['sort'];
            $arr['big_pic_url'] = $item['big_pic_url'];
            $arr['advert_pic'] = $item['advert_pic'];
            $arr['advert_url'] = $item['advert_url'];
            $arr['advert_open_type'] = $item['advert_open_type'];
            $arr['advert_params'] = $item['advert_params'];
            $arr['child'] = $item['child'];
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
