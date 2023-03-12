<?php

namespace app\plugins\wholesale\forms\mall;

use app\core\response\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\models\Mall;
use app\plugins\wholesale\models\WholesaleGoods;
use app\plugins\wholesale\Plugin;

/**
 * @property Mall $mall;
 */
class GoodsEditForm extends BaseGoodsEdit
{
    public $wholesale_rules;
    public $wholesale_type;
    public $rules_status;
    public $rise_num;

    public $wholesaleGoods;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['wholesale_rules'], 'safe'],
            [['wholesale_type', 'rules_status', 'rise_num'], 'integer'],
            [['wholesale_type', 'rules_status'], 'default', 'value' => 0],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'wholesale_rules' => '批发阶梯优惠',
            'wholesale_type' => '优惠方式',
            'rules_status' => '优惠规则开关',
            'rise_num' => '起批数'
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($this->rules_status == 1 && count($this->wholesale_rules) < 1) {
                throw new \Exception('阶梯优惠规则至少有一条');
            }
            $this->attrValidator();
            $this->setGoods();
            $this->setAttr();
            //优惠方式为减钱的时候，判断最低规格价是否小于最高减钱数
            if ($this->rules_status == 1) {
                $prevMinNum = 0;
                foreach ($this->wholesale_rules as $key => $wholesale_rule) {
                    if ($wholesale_rule['num'] <= $prevMinNum) {
                        throw new \Exception($this->parseChinese($key) . '级阶梯的件数必须大于上级阶梯');
                    }
                    $prevMinNum = $wholesale_rule['num'];
                }
                if ($this->wholesale_type == 1) {
                    $prevMinDiscount = 0;
                    $price_arr = [];
                    foreach ($this->newAttrs as $item) {
                        $price_arr[] = $item['price'];
                    }
                    $discount_arr = [];
                    foreach ($this->wholesale_rules as $key1 => &$wholesale_rule) {
                        if ($wholesale_rule['discount'] < $prevMinDiscount) {
                            throw new \Exception($this->parseChinese($key1) . '级阶梯的减钱不能小于上级阶梯');
                        }
                        $wholesale_rule['discount'] = price_format($wholesale_rule['discount']);
                        $prevMinDiscount = $wholesale_rule['discount'];
                        $discount_arr[] = $wholesale_rule['discount'];
                    }
                    unset($wholesale_rule);
                    if (min($price_arr) < max($discount_arr)) {
                        throw new \Exception('批发阶梯优惠金额必须小于等于最低规格售价');
                    }
                } else {
                    foreach ($this->wholesale_rules as $wholesale_rule) {
                        if (!($wholesale_rule['discount'] >= 0.1 && $wholesale_rule['discount'] <= 10)) {
                            throw new \Exception('折扣率不合法，折扣率必须在0.1折~10折。');
                        }
                    }
                }
            }
            $this->setCard();
            $this->setCoupon();
            $this->setGoodsService();
            $this->setListener();

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

    private function parseChinese($key)
    {
        switch ($key) {
            case 0:
                return '一';
            case 1:
                return '二';
            case 2:
                return '三';
            default:
                return '未知等级';
        }
    }

    protected function setGoodsSign()
    {
        return (new Plugin())->getName();
    }

    public function setExtraGoods($goods)
    {
        $wholesaleGoods = WholesaleGoods::findOne([
            'goods_id' => $goods->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$wholesaleGoods) {
            $wholesaleGoods = new WholesaleGoods();
            $wholesaleGoods->mall_id = \Yii::$app->mall->id;
            $wholesaleGoods->goods_id = $goods->id;
        }
        $wholesaleGoods->type = $this->wholesale_type;
        $wholesaleGoods->rules_status = $this->rules_status;
        $wholesaleGoods->rise_num = $this->rise_num;
        $wholesaleGoods->wholesale_rules = \Yii::$app->serializer->encode($this->wholesale_rules);
        $this->wholesaleGoods = $wholesaleGoods;
        $res = $wholesaleGoods->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($wholesaleGoods));
        }
    }
}
