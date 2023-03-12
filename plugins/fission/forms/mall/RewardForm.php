<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/16
 * Time: 4:17 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\forms\mall;

use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCards;
use app\plugins\fission\forms\Model;

class RewardForm extends Model
{
    public $status;
    public $people_number;
    public $model_id;
    public $exchange_type;
    public $min_number;
    public $max_number;
    public $send_type;
    public $level;
    public $attr_id;

    public function rules()
    {
        return [
            [['status', 'people_number'], 'required'],
            [['status'], 'in', 'range' => ['cash', 'balance', 'coupon', 'integral', 'goods', 'card']],
            [['people_number', 'model_id'], 'integer'],
            [['exchange_type'], 'default', 'value' => 'online'],
            ['exchange_type', 'in', 'range' => ['online', 'offline']],
            [['min_number', 'max_number'], 'number', 'min' => 0, 'max' => 999999, 'on' => ['balance', 'cash']],
            [['min_number', 'max_number'], 'integer', 'min' => 0, 'max' => 999999, 'on' => ['integral']],
            [['min_number', 'max_number'], 'default', 'value' => 0, 'on' => ['card', 'goods', 'coupon']],
            [['attr_id'], 'default', 'value' => 0, 'on' => ['card', 'coupon', 'balance', 'cash', 'integral']],
            [['attr_id'], 'required', 'on' => ['goods']],
            [['model_id'], 'default', 'value' => 0],
            ['send_type', 'default', 'value' => 'average'],
            [['send_type'], 'in', 'range' => ['random', 'average']],
            [['level'], 'in', 'range' => ['main', 'secondary']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'status' => '奖励种类',
            'people_number' => '邀请人数',
            'model_id' => '赠品、卡券、优惠券时为奖励的id',
            'exchange_type' => '赠品兑奖方式',
            'min_number' => '现金、余额、积分奖励时最小值',
            'max_number' => '现金、余额、积分奖励时最大值',
            'send_type' => '现金、余额、积分发放方式',
            'level' => '奖励等级',
            'attr_id' => '赠品规格',
        ];
    }

    public $errorMsg;

    public function check()
    {
        if (!$this->validate()) {
            $this->errorMsg = $this->getErrorMsg();
            return false;
        }
        switch ($this->status) {
            case 'cash':
            case 'balance':
            case 'integral':
                if (!$this->min_number) {
                    $this->errorMsg = '现金、余额、积分时，数量不能为空或为0';
                    return false;
                }
                if ($this->send_type == 'random') {
                    if (!$this->max_number) {
                        $this->errorMsg = '现金、余额、积分时，数量不能为空为0';
                        return false;
                    }
                    if ($this->min_number > $this->max_number) {
                        $temp = $this->max_number;
                        $this->max_number = $this->min_number;
                        $this->min_number = $temp;
                    }
                }
                if ($this->send_type == 'average') {
                    $this->max_number = $this->min_number;
                }
                break;
            case 'coupon':
                if (!$this->model_id) {
                    $this->errorMsg = '请选择优惠券';
                    return false;
                }
                $coupon = Coupon::find()->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->model_id
                ])->exists();
                if (!$coupon) {
                    $this->errorMsg = '所选的优惠券不存在，请更换';
                    return false;
                }
                break;
            case 'card':
                if (!$this->model_id) {
                    $this->errorMsg = '请选择卡券';
                    return false;
                }
                $card = GoodsCards::find()->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->model_id
                ])->exists();
                if (!$card) {
                    $this->errorMsg = '所选的卡券不存在，请更换';
                    return false;
                }
                break;
            case 'goods':
                if (!$this->model_id) {
                    $this->errorMsg = '请选择赠品';
                    return false;
                }
                $goods = Goods::find()->where([
                    'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->model_id
                ])->exists();
                if (!$goods) {
                    $this->errorMsg = '所选的商品不存在，请更换';
                    return false;
                }
                $attr = GoodsAttr::find()->where([
                    'goods_id' => $this->model_id, 'id' => $this->attr_id, 'is_delete' => 0
                ])->exists();
                if (!$attr) {
                    $this->errorMsg = '所选的商品的规格不存在，请更换';
                    return false;
                }
                break;
            default:
                $this->errorMsg = '错误的奖励类型';
                return false;
        }
        return true;
    }
}
