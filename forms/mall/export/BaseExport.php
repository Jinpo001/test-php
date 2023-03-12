<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;


use app\forms\common\platform\PlatformConfig;
use app\forms\mall\export\CsvExport;
use app\models\CoreFile;
use app\models\Model;
use app\models\ModelActiveRecord;
use app\models\User;

abstract class BaseExport extends Model
{
    public $fieldsKeyList;
    public $fieldsNameList;
    public $dataList;
    public $query;
    public $mall;
    public $mch_id;

    abstract public function fieldsList();

    abstract public function export($query);

    abstract protected function transform($list);

    protected function getFields()
    {
        $fieldsList = $this->fieldsList();
        $newFields = [];

        if ($this->fieldsKeyList) {
            foreach ($this->fieldsKeyList as $field) {
                foreach ($fieldsList as $item) {
                    if ($item['key'] === $field) {
                        $newFields[] = $item['value'];
                    }
                }
            }
        } else {
            $this->fieldsKeyList = [];
        }

        $this->fieldsNameList = $newFields;

        return $this->fieldsNameList;
    }

    protected function getDataList()
    {
        $newData = [];
        foreach ($this->dataList as $key => $item) {
            $arr = [];
            foreach ($this->fieldsKeyList as $fieldsKey) {
                if (isset($item[$fieldsKey])) {
                    $arr[] = $item[$fieldsKey];
                } else {
                    $arr[] = '';
                }
            }
            $newData[] = $arr;
        }
        return $newData;
    }

    protected function getPlatform($user)
    {
        $value = (new PlatformConfig())->getPlatformText($user);
        
        return $value;
    }

    protected function getDateTime($dateTime)
    {
        return (int)$dateTime > 0 ? (string)$dateTime : '';
    }

    // 废弃 不要再使用
    protected function getIsAddNumber()
    {
        return true;
    }

    public function getFileName()
    {
        return '数据列表';
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
            $coreFile->mall_id = \Yii::$app->mall->id;
            $coreFile->mch_id = $this->mch_id;
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
        return \Yii::$app->mall->id . '_' . $this->mch_id;
    }

    public function getOnlyFileName($id)
    {
        return sprintf('%s%s%s%s', $this->getFileName(), $id, time(), '.csv');
    }

    // 删除文件夹
    public function deleteDir($path) {

        if (is_dir($path)) {
            //扫描一个目录内的所有目录和文件并返回数组
            $dirs = scandir($path);

            foreach ($dirs as $dir) {
                //排除目录中的当前目录(.)和上一级目录(..)
                if ($dir != '.' && $dir != '..') {
                    //如果是目录则递归子目录，继续操作
                    $sonDir = $path.'/'.$dir;
                    if (is_dir($sonDir)) {
                        //目录内的子目录和文件删除后删除空目录
                        @rmdir($sonDir);
                    } else {
                        //如果是文件直接删除
                        @unlink($sonDir);
                    }
                }
            }
            @rmdir($path);
        }
    }
}
