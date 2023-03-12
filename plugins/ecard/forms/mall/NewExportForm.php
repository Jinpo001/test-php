<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/4
 * Time: 10:22 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\ecard\forms\mall;


use app\forms\mall\export\BaseExport;
use app\models\Ecard;
use app\models\EcardOptions;
use yii\helpers\Json;

class NewExportForm extends BaseExport
{
    /**
     * @var Ecard $ecard
     */
    public $ecard;

    public function fieldsList()
    {
        $list = Json::decode($this->ecard->list, true);
        $headList = [];
        foreach ($list as $key => $item) {
            $headList[] = [
                'key' => 'key' . $key,
                'value' => $item
            ];
        }
        $headList[] = [
            'key' => 'sales',
            'value' => '状态'
        ];
        return $headList;
    }

    public function export($query = null)
    {
        $this->fieldsKeyList = array_column($this->fieldsList(), 'key');
        $this->exportAction($this->query);
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        /* @var EcardOptions[] $list */
        foreach ($list as $item) {
            $value = Json::decode($item->value, true);
            $newItem = [];
            $newItem['number'] = $number++;
            foreach ($value as $key => $newValue) {
                $newItem['key' . $key] = $newValue['value'];
            }
            $newItem['sales'] = $item->is_sales == 1 ? '已售出' : '未售出';
            $newList[] = $newItem;
        }
        $this->dataList = $newList;
    }

    public function getFileName()
    {
        return '卡密模板--' . $this->ecard->name;
    }

    public function getFields()
    {
        $fieldsList = $this->fieldsList();
        $newFields = [];
        foreach ($fieldsList as $item) {
            $newFields[] = $item['value'];
        }

        \Yii::warning($this->ecard);
        \Yii::warning($newFields);
        $this->fieldsNameList = $newFields;

        return $this->fieldsNameList;
    }
}