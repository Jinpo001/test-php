<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/7/9
 * Time: 15:00
 */

namespace app\plugins\bonus\forms\export;

use app\core\CsvExport;
use app\forms\mall\export\BaseExport;
use app\models\CoreFile;
use app\models\User;
use app\models\UserInfo;
use app\plugins\bonus\models\BonusCaptain;
use yii\helpers\ArrayHelper;

class CaptainExport extends BaseExport
{
    public function fieldsList()
    {
        return [
            [
                'key' => 'nickname',
                'value' => '昵称',
            ],
            [
                'key' => 'name',
                'value' => '姓名',
            ],
            [
                'key' => 'mobile',
                'value' => '手机号',
            ],
            [
                'key' => 'all_bonus',
                'value' => '累计分红',
            ],
            [
                'key' => 'total_bonus',
                'value' => '可提现分红',
            ],
            [
                'key' => 'all_member',
                'value' => '团员数量',
            ],
            [
                'key' => 'created_at',
                'value' => '申请时间',
            ],
            [
                'key' => 'apply_at',
                'value' => '审核时间',
            ],
            [
                'key' => 'status',
                'value' => '状态'
            ]
        ];
    }

    public function export($query = null)
    {
        $query= $this->query;
        $list = $query->orderBy(['b.status' => SORT_ASC, 'b.created_at' => SORT_DESC])->all();

        $newList = [];
        /* @var BonusCaptain[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;

            $newItem = array_merge($newItem, [
                'nickname' => $user->nickname,

            ]);
            $newList[] = $newItem;
        }

        \Yii::warning('导出开始');
        try {
            $list = $newList;
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
        return '队长管理列表';
    }

    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $arr = [];
            $arr['number'] = $number++;
            $arr['id'] = $item['id'];
            $arr['nickname'] = $item['nickname'];
            $arr['name'] = $item['name'];
            $arr['mobile'] = $item['mobile'];
            $arr['created_at'] = $this->getDateTime($item['created_at']);
            $arr['apply_at'] = $this->getDateTime($item['apply_at']);
            switch ($item['status']) {
                case 0:
                    $arr['status'] = '审核中';
                    break;
                case 1:
                    $arr['status'] = '审核通过';
                    break;
                case 2:
                    $arr['status'] = '审核不通过';
                    break;
                default:
                    break;
            }
            $arr['all_bonus'] = price_format($item['all_bonus']);
            $arr['total_bonus'] = price_format($item['total_bonus']);
            $arr['all_member'] = $item['all_member'];

            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }
}