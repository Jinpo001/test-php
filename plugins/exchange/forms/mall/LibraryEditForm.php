<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\exchange\forms\common\CommonModel;
use app\plugins\exchange\models\ExchangeLibrary;

class LibraryEditForm extends Model
{
    public $name;
    public $remark;
    public $expire_type;
    public $expire_start_time;
    public $expire_end_time;
    public $expire_start_day;
    public $expire_end_day;
    public $mode;
    public $code_format;
    public $rewards;
    public $is_limit;
    public $limit_user_num;
    public $limit_user_success_num;
    public $limit_user_type;

    public $id;

    public function rules()
    {
        return [
            [['name', 'rewards', 'expire_type', 'mode', 'code_format'], 'required'],
            [['expire_start_day', 'expire_end_day', 'mode', 'id',
                'is_limit', 'limit_user_num', 'limit_user_success_num'], 'integer'],
            [['remark'], 'string'],
            [['rewards'], 'trim'],
            [['expire_start_time', 'expire_end_time'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['expire_type', 'code_format'], 'string', 'max' => 100],
            [['expire_type'], 'in', 'range' => ['all', 'fixed', 'relatively']],
            [['expire_start_time', 'expire_end_time'], 'default', 'value' => '0000-00-00 00:00:00'],
            [['expire_start_day', 'expire_end_day', 'mode', 'is_limit', 'limit_user_num', 'limit_user_success_num'], 'default', 'value' => 0],
            [['code_format'], 'in', 'range' => ['english_num', 'num']],
            [['remark'], 'default', 'value' => ''],
            [['limit_user_type'], 'default', 'value' => 'day'],
            [['expire_start_day', 'expire_end_day'], 'integer', 'max' => 999],
            [['limit_user_type'], 'string', 'max' => 8],
            [['limit_user_num', 'limit_user_success_num'], 'integer', 'max' => 999999],
        ];
    }
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'remark' => '说明',
            'expire_type' => 'all 永久 fixed 固定 relatively 相对',
            'expire_start_time' => '固定开始',
            'expire_end_time' => '固定开始',
            'expire_start_day' => '相对开始',
            'expire_end_day' => '相对结束',
            'mode' => '0 全部 1 份',
            'code_format' => 'english_num, num',
            'rewards' => '奖励品',
            'rewards_s' => '奖励品类型 后台搜索使用',
            'is_limit' => '限制兑换',
            'limit_user_num' => '每位用户每天限制兑换成功次数',
            'limit_user_success_num' => '永久兑换成功次数字',
            'limit_user_type' => '限制兑换类型 day all',
        ];
    }

    //仅新增
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $this->id and die('禁止修改');
            $rewards = CommonModel::setFormatRewards($this->rewards);
            if (empty($rewards)) {
                throw new \Exception('奖励品不能为空');
            }
            if (count($rewards) > 20) {
                throw new \Exception('奖励品过多');
            }
            if ($this->expire_type === 'relatively') {
                if ($this->expire_start_day <= 0 || $this->expire_start_day >= $this->expire_end_day) {
                    throw new \Exception('请正确配置相对时间');
                }
            }

            $model = new ExchangeLibrary();
            $model->attributes = $this->attributes;
            $model->mall_id = \Yii::$app->mall->id;
            $model->rewards = \yii\helpers\BaseJson::encode($rewards);
            $model->rewards_s = implode(',', array_unique(array_map(function ($reward) {
                return $reward['type'];
            }, $rewards)));
            $model->is_recycle = 0;
            $model->recycle_at = '0000-00-00 00:00:00';
            if (!$model->save()) {
                throw new \Exception($this->getErrorMsg($model));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
