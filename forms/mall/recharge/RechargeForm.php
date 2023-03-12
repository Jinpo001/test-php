<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\recharge;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Recharge;

class RechargeForm extends Model
{
    public $id;
    public $keyword;
    public $mall_id;
    public $name;
    public $pay_price;
    public $send_price;
    public $is_delete;
    public $send_integral;
    public $send_member_id;

    public $send_type;
    public $send_card;
    public $send_coupon;
    public $lottery_limit;

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['id', 'mall_id', 'is_delete', 'send_integral', 'lottery_limit'], 'integer'],
            [['pay_price', 'send_price'], 'number'],
            [['is_delete', 'send_price', 'send_integral', 'send_member_id', 'lottery_limit'], 'default', 'value' => 0],
            [['keyword'], 'string'],
            [['pay_price', 'send_price', 'lottery_limit'], 'number', 'max' => 99999999],
            [['keyword'], 'default', 'value' => 0],
            [['pay_price', 'send_price'], 'number', 'min' => 0],
            [['send_integral'], 'integer', 'min' => 0],
            [['send_card', 'send_coupon', 'send_type'], 'trim'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'mall ID',
            'name' => '名称',
            'pay_price' => '支付价格',
            'send_price' => '赠送价格',
            'is_delete' => '删除',
            'send_integral' => '赠送积分',
            'send_member_id' => '赠送会员',
            'send_type' => '赠送类型',
            'send_card' => '赠送卡券',
            'send_coupon' => '赠送优惠券',
            'lottery_limit' => '抽奖次数',
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        $query = Recharge::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);

        $list = $query->keyword($this->keyword, ['like', 'name', $this->keyword])
            ->with('member')
            ->orderBy('id DESC,created_at DESC')
            ->asArray()
            ->all();

        $list = array_map(function ($item) {
            $item['send_coupon']  = \yii\helpers\BaseJson::decode($item['send_coupon'] ?: '{}');
            $item['send_card'] = \yii\helpers\BaseJson::decode($item['send_card'] ?: '{}');
            return $item;
        }, $list);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //DELETE
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Recharge::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    //DELETE
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $list = Recharge::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ])
            ->with('member')
            ->asArray()
            ->one();

        $send_type = [];
        foreach (Recharge::R_ALL as $item) {
            $list['send_type'] & $item && array_push($send_type, $item);
        }
        $list['send_type'] = $send_type;
        $list['send_coupon'] = \yii\helpers\BaseJson::decode($list['send_coupon'] ?: '{}');
        $list['send_card'] = \yii\helpers\BaseJson::decode($list['send_card'] ?: '{}');
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ]
        ];
    }

    //SAVE
    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $model = Recharge::findOne([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ]);
        if (!$model) {
            $model = new Recharge();
        }
        if ($this->pay_price <= 0) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => '支付金额必须大于0'
            ];
        }

        $model->attributes = $this->attributes;
        $model->send_card = \yii\helpers\BaseJson::encode($this->send_card);
        $model->send_coupon = \yii\helpers\BaseJson::encode($this->send_coupon);
        $model->send_type = array_sum($this->send_type ?: []);
        $model->mall_id = \Yii::$app->mall->id;
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->getErrorResponse($model);
        }
    }
}
