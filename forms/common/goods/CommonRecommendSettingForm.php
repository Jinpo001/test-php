<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\forms\common\goods;

use app\forms\common\CommonOption;
use app\models\Model;
use app\models\Option;

class CommonRecommendSettingForm extends Model
{
    public function getSetting()
    {
        $setting = CommonOption::get(
            Option::NAME_RECOMMEND_SETTING,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            $this->getDefault()
        );
        $setting = \yii\helpers\ArrayHelper::toArray($setting);
        $setting = array_merge($this->getDefault(),$setting);
        foreach ($setting as $key => &$item) {
            if (isset($item['is_recommend_status'])) {
                $item['is_recommend_status'] = (int)$item['is_recommend_status'];
            }
            if (isset($item['is_custom'])) {
                $item['is_custom'] = (int)$item['is_custom'];
            }
        }
        return $setting;
    }

    public function getDefault()
    {
        return [
            'goods' => [
                'is_recommend_status' => 1,
                'goods_num' => 6
            ],
            'order_pay' => [
                'is_recommend_status' => 1,
                'is_custom' => 0,
                'goods_list' => []
            ],
            'order_comment' => [
                'is_recommend_status' => 1,
                'is_custom' => 0,
                'goods_list' => []
            ],
            // 列表样式 -1 列表模式 0 左右滑动 1 一行一个 2 一行两个 3 一行三个
            'comment_style' => [
                'pic_url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/goods/icon-favorite.png',
                'text' => '您或许会喜欢',
                'text_color' => '#999999',
                'list_style' => '2'
            ]
        ];
    }
}