<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\wxapp\forms\wx_app_config;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\wxapp\models\WxappWxminiprograms;
use app\plugins\wxapp\models\WxappConfig;
use yii\helpers\ArrayHelper;

class WxAppConfigForm extends Model
{
    public $id;
    public $page;


    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '微信配置ID',
        ];
    }

    public function getDetail()
    {
        $third = WxappWxminiprograms::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if ($third) {
            $third = ArrayHelper::toArray($third);
            if (!empty($third['domain'])) {
                $third['domain'] = explode(",", trim($third['domain'], ','));
            } else {
                $third['domain'] = [];
            }
        }
        /**@var WxappConfig $detail**/
        $detail = WxappConfig::find()
            ->where(['mall_id' => \Yii::$app->mall->id])
            ->one();

        $newDetail = [];
        if ($detail) {
            $newDetail = ArrayHelper::toArray($detail);
        }

        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->adminInfo);
        if (!$third && !$detail) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '信息未配置',
                'data' => [
                    'has_third_permission' => in_array('wxplatform', $permission),
                    'has_fast_create_wxapp_permission' => in_array('fast-create-wxapp', $permission),
                ]
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $newDetail,
                'third' => $third,
                'has_third_permission' => in_array('wxplatform', $permission),
                'has_fast_create_wxapp_permission' => in_array('fast-create-wxapp', $permission),
            ]
        ];
    }
}
