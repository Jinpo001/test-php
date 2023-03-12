<?php

namespace app\plugins\wholesale\forms\mall;

use app\core\response\ApiCode;
use app\models\MallMembers;
use app\models\Model;
use app\models\Option;
use app\plugins\wholesale\forms\common\SettingForm;

class WholesaleSettingForm extends Model
{
    public $id;
    public $is_share;
    public $is_territorial_limitation;
    public $is_coupon;
    public $is_member_price;
    public $is_integral;
    public $svip_status;
    public $banner;
    public $is_vip_show;
    public $vip_show_limit;

    public function rules()
    {
        return [
            [['id', 'is_share', 'is_territorial_limitation', 'is_coupon', 'is_member_price', 'is_integral', 'svip_status', 'is_vip_show'], 'integer'],
            [['banner', 'vip_show_limit'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_coupon' => '使用优惠券',
            'is_member_price' => '是否启用会员价',
            'is_integral' => '是否使用积分',
            'svip_status' => '超级会员卡',
            'banner' => '广告图'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->is_vip_show && empty(json_decode($this->vip_show_limit, true))) {
                throw new \Exception('请选择会员等级');
            }
            $array = [
                'is_share' => $this->is_share,
                'is_territorial_limitation' => $this->is_territorial_limitation,
                'is_coupon' => $this->is_coupon,
                'is_member_price' => $this->is_member_price,
                'is_integral' => $this->is_integral,
                'svip_status' => $this->svip_status,
                'banner' => $this->banner,
                'is_vip_show' => $this->is_vip_show,
                'vip_show_limit' => $this->vip_show_limit
            ];

            $result = \app\forms\common\CommonOption::set('wholesale_setting', $array, \Yii::$app->mall->id, Option::GROUP_ADMIN);
            if (!$result) {
                throw new \Exception('保存失败');
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }


    public function getSetting()
    {
        $setting = (new SettingForm())->search();
        if (isset($setting['vip_show_limit'])) {
            $setting['vip_show_limit'] = MallMembers::find()->where(['mall_id' => \Yii::$app->mall->id, 'level' => $setting['vip_show_limit'], 'is_delete' => 0, 'status' => 1])->select(['level', 'name'])->asArray()->all();
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'setting' => $setting,
            ]
        ];
    }
}
