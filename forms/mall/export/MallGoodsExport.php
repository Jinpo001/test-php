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
use app\forms\mall\goods\ImportDataLogForm;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\CoreFile;
use app\models\Goods;
use yii\helpers\ArrayHelper;

class MallGoodsExport extends BaseExport
{
    public function fieldsList()
    {
        $fieldsList = [
            [
                'key' => 'id',
                'value' => '商品ID',
            ],
            [
                'key' => 'name',
                'value' => '商品名称',
            ],
            [
                'key' => 'original_price',
                'value' => '原价',
            ],
            [
                'key' => 'cost_price',
                'value' => '成本价',
            ],
            [
                'key' => 'detail',
                'value' => '商品详情',
            ],
            [
                'key' => 'cover_pic',
                'value' => '商品缩略图',
            ],
            [
                'key' => 'pic_url',
                'value' => '商品轮播图',
            ],
            [
                'key' => 'video_url',
                'value' => '商品视频',
            ],
            [
                'key' => 'unit',
                'value' => '单位',
            ],
            [
                'key' => 'price',
                'value' => '售价',
            ],
            [
                'key' => 'use_attr',
                'value' => '是否使用规格',
            ],
            [
                'key' => 'attr_groups',
                'value' => '规格组',
            ],
            [
                'key' => 'goods_stock',
                'value' => '商品库存',
            ],
            [
                'key' => 'virtual_sales',
                'value' => '虚拟销量',
            ],
            [
                'key' => 'confine_count',
                'value' => '购物数量限制',
            ],
            [
                'key' => 'pieces',
                'value' => '单品满件包邮',
            ],
            [
                'key' => 'forehead',
                'value' => '单品满额包邮',
            ],
            [
                'key' => 'give_integral',
                'value' => '赠送积分',
            ],
            [
                'key' => 'give_integral_type',
                'value' => '赠送积分类型',
            ],
            [
                'key' => 'forehead_integral',
                'value' => '可抵扣积分',
            ],
            [
                'key' => 'forehead_integral_type',
                'value' => '可抵扣积分类型',
            ],
            [
                'key' => 'accumulative',
                'value' => '允许多件累计折扣',
            ],
            [
                'key' => 'sign',
                'value' => '商品标识',
            ],
            [
                'key' => 'app_share_pic',
                'value' => '自定义分享图片',
            ],
            [
                'key' => 'app_share_title',
                'value' => '自定义分享标题',
            ],
            [
                'key' => 'sort',
                'value' => '排序',
            ],
            [
                'key' => 'confine_order_count',
                'value' => '限购订单',
            ],
            [
                'key' => 'is_area_limit',
                'value' => '是否单独区域购买',
            ],
            [
                'key' => 'area_limit',
                'value' => '区域限购详情',
            ],
            [
                'key' => 'attr',
                'value' => '规格详情',
            ],
            [
                'key' => 'is_quick_shop',
                'value' => '是否快速购买',
            ],
            [
                'key' => 'is_sell_well',
                'value' => '是否热销',
            ],
            [
                'key' => 'is_negotiable',
                'value' => '是否面议',
            ],
            [
                'key' => 'shipping_id',
                'value' => '包邮规则',
            ],
        ];

        return $fieldsList;
    }

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


        $query = $this->query;
        $query->with('goodsWarehouse', 'attr', 'mallGoods')->orderBy('g.created_at DESC');

        \Yii::warning('导出开始');
        try {
            // 获取数据总数
            $query2 = clone $query;
            $count =$query2->count();

            $fieldsNameList = $this->getFields();

            // 文件夹唯一标识
            $id = \Yii::$app->mall->id . '_' . $this->mch_id;
            // 临时 文件夹唯一标识
            $temporaryId = sprintf('%s_%s%s%s', \Yii::$app->mall->id, $this->mch_id, '/goods_', time());
            // 唯一文件名称
            $zipFileName = sprintf('%s%s%s%s', $this->getFileName(), $id, time(), '.zip');

            $coreFile = new CoreFile();
            $coreFile->mall_id = \Yii::$app->mall->id;
            $coreFile->mch_id = $this->mch_id;
            $coreFile->file_name = $zipFileName;

            $currentCount = 0;
            $temporaryFileNameList = [];
            foreach ($query->batch(300) as $item) {
                // 临时 唯一文件名称
                $temporaryFileName = sprintf('%s%s%s', $this->getFileName(), time(), '.csv');
                $temporaryFileNameList[] = $temporaryFileName;
                $this->transform($item);
                $dataList = $this->getDataList();
                (new CsvExport())->newAjaxExport($dataList, $fieldsNameList, $temporaryFileName, $temporaryId);

                $currentCount += count($item);
                $percent = price_format($currentCount / $count);
                $coreFile->percent = $percent;
                $res = $coreFile->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($coreFile));
                }
            }

            if ($currentCount > 0) {
                // 生成的临时文件目录
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
        return '商品列表';
    }

    protected function transform($list)
    {
        $newList = [];
        /** @var Goods $item */
        foreach ($list as $item) {
            $arr = [];
            $arr['id'] = $item->id;
            $arr['name'] = $item->name;
            $arr['original_price'] = $item->originalPrice;
            $arr['cost_price'] = $item->costPrice;
            $arr['detail'] = $item->detail;
            $arr['cover_pic'] = $item->coverPic;
            $arr['pic_url'] = $item->picUrl;
            $arr['video_url'] = $item->videoUrl;
            $arr['unit'] = $item->unit;
            $arr['price'] = $item->price;
            $arr['use_attr'] = $item->use_attr;
            $arr['attr_groups'] = $item->attr_groups;
            $arr['goods_stock'] = $item->goods_stock;
            $arr['virtual_sales'] = $item->virtual_sales;
            $arr['confine_count'] = $item->confine_count;
            $arr['pieces'] = $item->pieces;
            $arr['forehead'] = $item->forehead;
            $arr['give_integral'] = $item->give_integral;
            $arr['give_integral_type'] = $item->give_integral_type;
            $arr['forehead_integral'] = $item->forehead_integral;
            $arr['forehead_integral_type'] = $item->forehead_integral_type;
            $arr['accumulative'] = $item->accumulative;
            $arr['sign'] = $item->sign;
            $arr['app_share_pic'] = $item->app_share_pic;
            $arr['app_share_title'] = $item->app_share_title;
            $arr['sort'] = $item->sort;
            $arr['confine_order_count'] = $item->confine_order_count;
            $arr['is_area_limit'] = $item->is_area_limit;
            $arr['area_limit'] = $item->area_limit;
            $arr['shipping_id'] = $item->shipping_id;
            $newAttr = ArrayHelper::toArray($item->attr);
            $attrGroups = \Yii::$app->serializer->decode($item->attr_groups);
            $attrList = $item->resetAttr($attrGroups);
            foreach ($newAttr as $key => $attrItem) {
                $newAttr[$key]['attr_list'] = $attrList[$attrItem['sign_id']];
            }
            $arr['attr'] = json_encode($newAttr, true);
            $arr['is_quick_shop'] = $item->mallGoods->is_quick_shop;
            $arr['is_sell_well'] = $item->mallGoods->is_sell_well;
            $arr['is_negotiable'] = $item->mallGoods->is_negotiable;
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
