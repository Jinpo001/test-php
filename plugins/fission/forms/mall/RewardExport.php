<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/29
 * Time: 2:38 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\forms\mall\export\BaseExport;
use app\plugins\fission\models\FissionActivityLog;

class RewardExport extends BaseExport
{

    public function fieldsList()
    {
        return [
            [
                'key' => 'user',
                'value' => '用户',
            ],
            [
                'key' => 'real_reward',
                'value' => '红包金额',
            ],
            [
                'key' => 'created_at',
                'value' => '参与时间',
            ],
        ];
    }

    public function export($query = null)
    {
        $this->fieldsKeyList = array_column($this->fieldsList(), 'key');
        $this->exportAction($this->query);
    }

    /**
     * @param FissionActivityLog[] $list
     */
    protected function transform($list)
    {
        $newList = [];
        $number = 1;
        foreach ($list as $item) {
            $user = $item->first->user;
            $arr = [];
            $arr['number'] = $number++;
            $arr['user'] = sprintf('(%d)%s', $user['id'], $user['nickname']);
            $arr['real_reward'] = floatval($item->first->real_reward);
            $arr['created_at'] = $item->first->created_at;
            $newList[] = $arr;
        }

        $this->dataList = $newList;
    }

    /**
     * 获取csv名称
     * @return string
     */
    public function getFileName()
    {
        return '红包记录详情';
    }
}
