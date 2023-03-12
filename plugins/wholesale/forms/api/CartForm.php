<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\wholesale\forms\api;

use app\core\response\ApiCode;
use app\events\CartEvent;
use app\forms\common\goods\GoodsAuth;
use app\models\Cart;
use app\models\GoodsAttr;
use app\models\GoodsMemberPrice;
use app\models\MallMembers;
use app\models\Model;
use app\models\UserIdentity;
use app\plugins\wholesale\forms\common\SettingForm;
use app\plugins\wholesale\models\WholesaleGoods;
use app\plugins\wholesale\models\Goods;
use app\plugins\wholesale\Plugin;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class CartForm extends Model
{
    public $attr;

    public $goods_id;
    public $attr_id;
    public $num;

    public function rules()
    {
        return [
//            [['goods_id', 'num'], 'integer'],
//            [['attr_id'], 'safe'],
            [['attr'], 'string'],
        ];
    }

    public function addCart()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->attr) {
                throw new Exception('Attr不能为空');
            }
            $attr_arr = json_decode($this->attr, true);
            $this->goods_id = $attr_arr[0]['goods_id'];

            $Goods = WholesaleGoods::find()->where([
                'goods_id' => $this->goods_id,
                'is_delete' => 0,
            ])->with('attr')->one();

            if (!$Goods) {
                throw new \Exception('批发商品不存在');
            }
            foreach ($attr_arr as $item) {
                $this->attr_id = $item['id'];
                $this->num = $item['number'];

                $attr = null;
                foreach ($Goods->attr as $aItem) {
                    if ($aItem->id == $this->attr_id) {
                        $attr = $aItem;
                        if ($aItem->stock < $this->num) {
                            throw new \Exception('商品库存不足');
                        }
                    }
                }
                if (!$attr) {
                    throw new \Exception('商品规格异常');
                }

                $cart = Cart::findOne([
                    'user_id' => \Yii::$app->user->id,
                    'goods_id' => $this->goods_id,
                    'attr_id' => $this->attr_id,
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                ]);

                if (!$cart) {
                    $cart = new Cart();
                    $cart->mall_id = \Yii::$app->mall->id;
                    $cart->user_id = \Yii::$app->user->id;
                    $cart->goods_id = $this->goods_id;
                    $cart->attr_id = $this->attr_id;
                    $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
                    $cart->sign = \Yii::$app->plugin->getCurrentPlugin()->getName();
                };
                $cart->num += $this->num;
                $res = $cart->save();
                if (!$res) {
                    throw new \Exception($this->getErrorMsg($cart));
                }

                \Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加购物车成功',
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function getCartList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        \Yii::$app->db->createCommand("SET SESSION group_concat_max_len=102400;")->query();
        $query = Cart::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'user_id' => \Yii::$app->user->id,
            'sign' => (new Plugin())->getName(),
        ])->select(new Expression("id,goods_id,mch_id,`sign`,GROUP_CONCAT('{\"id\":\"',id,'\",\"attr_id\":\"',attr_id,'\",\"num\":\"',num,'\",\"attr_info\":',attr_info,'}') attr_arr"))
            ->groupBy('goods_id')->with(['goods.goodsWarehouse']);

        $list = $query->asArray()->all();

        $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
        $setting = (new SettingForm())->search();
        $is_negotiable = 0;
        if ($setting['is_vip_show']) {
            $vip_arr = $setting['vip_show_limit'];
            if (!empty($vip_arr)) {
                $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
                if ($userIdentity && !in_array($userIdentity->member_level, $vip_arr)) {
                    $is_negotiable = 1;
                }
            } else {
                $is_negotiable = 1;
            }
        }
        if ($is_negotiable == 1) {
            return [];
        }
        $sendType = \Yii::$app->mall->getMallSettingOne('send_type');
        $newList = [];
        foreach ($list as $item) {
            $newItem = $item;
            if (isset($item['goods'])) {
                $goodsAuth = GoodsAuth::create($item['goods']['sign']);
                $newItem['goods'] = $item['goods'];
                $newItem['send_type'] = $goodsAuth->getSendType((object)$item['goods'], $sendType);
                $newItem['sell_time'] = $goodsAuth->getSellTime((object)$item['goods']);
                $newItem['is_finish_sell'] = $goodsAuth->checkFinishSell((object)$item['goods']);
                $newItem['goods']['status'] = $goodsAuth->checkFinishSell((object)$item['goods']) ?
                    0 : $newItem['goods']['status'];
            } else {
                $newItem['goods'] = [];
                $newItem['send_type'] = $sendType;
                $newItem['sell_time'] = 0;
            }
            if (isset($item['goods']['goodsWarehouse'])) {
                $newItem['goods']['goodsWarehouse'] = $item['goods']['goodsWarehouse'];
                $newItem['goods']['name'] = $item['goods']['goodsWarehouse']['name'];
                $newItem['goods']['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'];
            } else {
                $newItem['goods']['goodsWarehouse'] = [];
                $newItem['goods']['name'] = '';
                $newItem['goods']['cover_pic'] = '';
            }
            $newItem['buy_goods_auth'] = true;

            $newItem['attr_arr'] = json_decode('[' . $item['attr_arr'] . ']', true);
            foreach ($newItem['attr_arr'] as $key => &$attr_item) {
                $attr_item['reduce_price'] = 0;
                $attr_item['is_active'] = false;
                $attrs = GoodsAttr::find()->where(['id' => $attr_item['attr_id'], 'is_delete' => 0])->with('memberPrice')->one();
                $attr_item['attrs'] = $attrs ? ArrayHelper::toArray($attrs) : $attrs;
                if ($attrs) {
                    $attr_item['attrs']['memberPrice'] = isset($attrs->memberPrice) ? ArrayHelper::toArray($attrs->memberPrice) : [];
                    // 还存在的商品
                    $attr_item['attrs']['attr'] = (new Goods())->signToAttr($attrs->sign_id, $item['goods']['attr_groups']);
                    $attr_item['attr_str'] = 0;
                    if ($attr_item['attr_info']) {
                        try {
                            $attrInfo = $attr_item['attr_info'];
                            $reducePrice = $attrInfo['price'] - $attrs->price;
                            if ($attrInfo['price'] - $attrs->price) {
                                $attr_item['reduce_price'] = price_format($reducePrice);
                            }
                        } catch (\Exception $exception) {
                        }
                    }
                    if ($setting['is_member_price'] && $userIdentity && $userIdentity->member_level && $item['goods']['is_level']) {
                        if ($item['goods']['is_level_alone']) {
                            /** @var GoodsMemberPrice $mItem */
                            foreach ($attrs->memberPrice as $mItem) {
                                if ($mItem->level == $userIdentity->member_level) {
                                    $attr_item['attrs']['price'] = $mItem->price > 0 ? $mItem->price : $attrs->price;
                                    break;
                                }
                            }
                        } else {
                            /** @var MallMembers $member */
                            $member = MallMembers::find()->where([
                                'status' => 1,
                                'is_delete' => 0,
                                'level' => $userIdentity->member_level,
                                'mall_id' => \Yii::$app->mall->id
                            ])->one();
                            if ($member) {
                                $attr_item['attrs']['price'] = round(($member->discount / 10) * $attrs->price, 2);
                            }
                        }
                    }
                    $newItem['attrs']['price'] = isset($newItem['attrs']['price']) ? $newItem['attrs']['price'] + $attr_item['attrs']['price'] : $attr_item['attrs']['price'];
                    $newItem['attrs']['price'] = price_format($newItem['attrs']['price']);
                    $newItem['attrs']['num'] = isset($newItem['attrs']['num']) ? $newItem['attrs']['num'] + $attr_item['num'] : $attr_item['num'];
                } else {
                    $attr_item['attr_str'] = 1;
                }
            }
            $newItem['plugin_data'] = $this->getCartExtraData($item['goods_id']);

            $newList[] = $newItem;
        }

        return $newList;
    }

    /**
     * @param $goods_id
     * @return array
     * 购物车商品额外限制条件信息
     */
    public function getCartExtraData($goods_id)
    {
        $data = [
            'up_num' => 0,//起购数
            'limit_num' => 0,//限购数
            'discount_type' => 0,//优惠方式，0折扣，1减钱
            'discount_rules' => ''//优惠规则
        ];
        $goods_info = WholesaleGoods::findOne(['goods_id' => $goods_id, 'is_delete' => 0]);
        if (!empty($goods_info)) {
            $data = [
                'up_num' => $goods_info->rise_num ?? 0,//起购数
                'limit_num' => 0,//限购数
                'discount_type' => $goods_info->type ?? 0,//优惠方式，0折扣，1减钱
                'discount_rules' => json_decode($goods_info->wholesale_rules ?? [], true)//优惠规则
            ];
        }

        return $data;
    }
}
