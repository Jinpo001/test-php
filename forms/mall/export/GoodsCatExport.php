<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\mall\export;

use app\core\CsvExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\GoodsCats;
use yii\helpers\ArrayHelper;

class GoodsCatExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'name',
                'value' => '分类名称',
            ],
            [
                'key' => 'pic_url',
                'value' => '分类图标',
            ],
            [
                'key' => 'sort',
                'value' => '排序',
            ],
            [
                'key' => 'big_pic_url',
                'value' => '分类背景大图',
            ],
            [
                'key' => 'advert_pic',
                'value' => '广告图片',
            ],
            [
                'key' => 'advert_url',
                'value' => '广告链接',
            ],
            [
                'key' => 'advert_open_type',
                'value' => '打开方式',
            ],
            [
                'key' => 'advert_params',
                'value' => '导航参数',
            ],
            [
                'key' => 'child',
                'value' => '子分类',
            ],
        ];
    }

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

        $query = $this->query->with(['child.child']);
        $this->exportAction($query);

        return true;
    }

    public function getFileName()
    {
        return '分类列表';
    }

    protected function transform($list)
    {
        $newList = [];
        /** @var GoodsCats $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['name'] = $item->name;
            $arr['pic_url'] = $item->pic_url;
            $arr['sort'] = $item->sort;
            $arr['big_pic_url'] = $item->big_pic_url;
            $arr['advert_pic'] = $item->advert_pic;
            $arr['advert_url'] = $item->advert_url;
            $arr['advert_open_type'] = $item->advert_open_type;
            $arr['advert_params'] = $item->advert_params;
            $newChild = [];
            /** @var GoodsCats $secondChild */
            foreach ($item->child as $secondChild) {
                $newItem = ArrayHelper::toArray($secondChild);
                $newItem['child'] = $secondChild->child ? ArrayHelper::toArray($secondChild->child) : [];
                $newChild[] = $newItem;
            }
            $arr['child'] = json_encode($newChild, true);
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}
