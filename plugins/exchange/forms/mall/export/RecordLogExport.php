<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\exchange\forms\mall\export;

use app\core\CsvExport;
use app\core\response\ApiCode;
use app\forms\common\platform\PlatformConfig;
use app\forms\mall\export\BaseExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\plugins\exchange\forms\common\CommonModel;

class RecordLogExport extends BaseExport
{
    public $library_id;

    public function fieldsList()
    {
        return [
            [
                'key' => 'code',
                'value' => '兑换码',
            ],
            [
                'key' => 'platform',
                'value' => '兑换渠道',
            ],
            [
                'key' => 'user_id',
                'value' => '用户ID',
            ],
            [
                'key' => 'nickname',
                'value' => '兑换会员',
            ],
            [
                'key' => 'rewards_text',
                'value' => '兑换奖励',
            ],
            [
                'key' => 'r_raffled_at',
                'value' => '兑换时间',
            ],
        ];
    }

    public function export($query = null)
    {
        $query = $this->query;
        $this->fieldsKeyList = array_column($this->fieldsList(), 'key');

        $this->exportAction($query);

        return true;
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        $library = CommonModel::getLibrary($this->library_id);
        $library && $name = $library->name;
        return sprintf('%s-兑换记录', $name ?? '');
    }

    protected function transform($list)
    {
        $newList = [];
        foreach ($list as $item) {
            $arr = [];
            $arr['code'] = $item->code;
            $arr['platform'] = CommonModel::getPlatform((PlatformConfig::getInstance())->getPlatform($item->user));
            $arr['user_id'] = $item->user->userInfo->user_id ?? '';
            $arr['nickname'] = $item->user->nickname ?? '';
            $rewards = \yii\helpers\BaseJson::decode($item['r_rewards']);
            $arr['rewards_text'] =  implode(',', array_unique(array_map(function ($reward) {
                return $reward['name'];
            }, $rewards)));
            $arr['r_raffled_at'] = $item->r_raffled_at;
            $newList[] = $arr;
        }
        $this->dataList = $newList;
    }
}
