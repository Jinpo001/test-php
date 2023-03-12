<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: zbj
 */

namespace app\plugins\wholesale\forms\api;

use app\core\response\ApiCode;
use app\forms\common\CommonUser;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\CommonGoodsMember;
use app\forms\common\goods\CommonGoodsVipCard;
use app\forms\common\template\TemplateList;
use app\models\Model;
use app\models\User;
use app\models\UserIdentity;
use app\plugins\wholesale\forms\common\CommonForm;
use app\plugins\wholesale\forms\common\SettingForm;
use app\plugins\wholesale\models\WholesaleGoods;
use app\plugins\wholesale\models\Goods;
use app\plugins\wholesale\Plugin;
use yii\helpers\ArrayHelper;

class GoodsForm extends Model
{
    public $id;
    public $page;
    public $goods_id;
    public $keyword;
    public $cat_id;

    public function rules()
    {
        return [
            [['page', 'goods_id', 'id', 'cat_id'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', "value" => 1]
        ];
    }

    public function getList($goods_ids = null)
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        $form = new CommonGoodsList();
        $form->model = 'app\plugins\wholesale\models\Goods';
        if ($this->goods_id) {
            $wholesale_goods = Goods::find()->with('cat')->where(['id' => $this->goods_id])->one();
            if (empty($wholesale_goods)) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '参数错误',
                ];
            }
            $form->cat_id = $wholesale_goods->cat->cat_id;
        }
        $form->page = $this->page;
        $form->keyword = $this->keyword;
        $form->sign = 'wholesale';
        $form->sort = 1;
        $form->relations = ['goodsWarehouse.cats', 'attr', 'wholesaleGoods'];
        $form->is_array = 1;
        $form->status = 1;
        $form->cat_id = $this->cat_id;
        $form->getQuery();
        if ($goods_ids) {
            $form->limit = 9999;
            $form->goods_id = $goods_ids;
        }
        if ($this->goods_id) {
            $form->query->andWhere(['<>', 'ag.goods_id', $this->goods_id]);
        }
        $list = $form->getList();

        $is_negotiable = 1;
        $setting = (new SettingForm())->search();
        if ($setting['is_vip_show']) {
            $vip_arr = $setting['vip_show_limit'];
            if (!empty($vip_arr)) {
                $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
                if ($userIdentity && in_array($userIdentity->member_level, $vip_arr)) {
                    $is_negotiable = 0;
                }
            } else {
                $is_negotiable = 0;
            }
        } else {
            $is_negotiable = 0;
        }
        $banner = $setting['banner'];
        foreach ($list as &$item) {
            $item['is_negotiable'] = $is_negotiable;
            //批发相关处理
            CommonForm::getWholesalePrice($item);
            unset($item['attr']);
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list,
                'banner' => $banner,
                'pagination' => $form->pagination,
            ]
        ];
    }

    public function detail()
    {
        try {
            $form = new CommonGoodsDetail();
            $form->mall = \Yii::$app->mall;
            $form->user = User::findOne(\Yii::$app->user->id);
            $goods = $form->getGoods($this->id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status != 1) {
                throw new \Exception('商品未上架');
            }
            $form->goods = $goods;
            $form->setIsLimitBuy(true);
            $goods = $form->getAll();

            $wholesaleGoods = ArrayHelper::toArray(WholesaleGoods::findOne(['goods_id' => $goods['id']]));
            $wholesaleGoods['wholesale_rules'] = ($wholesaleGoods['wholesale_rules'] == '[]') ? [] : $wholesaleGoods['wholesale_rules'];
            $goods['wholesaleGoods'] = $wholesaleGoods;

            $setting = (new SettingForm())->search();
            $goods['goods_marketing']['limit'] = $setting['is_territorial_limitation']
                ? $goods['goods_marketing']['limit'] : '';


            // 判断插件分销是否开启
            if (!$setting['is_share']) {
                $goods['share'] = 0;
            }

            try {
                $goods['template_message'] = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, [
                    'pay_wholesale_balance',
                ]);
            } catch (\Exception $exception) {
                $goods['template_message'] = [];
            }

            $goods['level_show'] = $setting['is_member_price'] ? $goods['level_show'] : 0;
            $goods['level_price'] = CommonGoodsMember::getCommon()->getGoodsMemberPrice((object)$goods);
            $goods['is_level'] = $setting['is_member_price'] ? $goods['is_level'] : 0;
            $goods['vip_card_appoint'] = CommonGoodsVipCard::getInstance()->setGoods($goods)->getAppoint();
            $is_negotiable = 1;
            $setting = (new SettingForm())->search();
            if ($setting['is_vip_show']) {
                $vip_arr = $setting['vip_show_limit'];
                if (!empty($vip_arr)) {
                    $userIdentity = UserIdentity::findOne(['user_id' => \Yii::$app->user->id]);
                    if ($userIdentity && in_array($userIdentity->member_level, $vip_arr)) {
                        $is_negotiable = 0;
                    }
                } else {
                    $is_negotiable = 0;
                }
            } else {
                $is_negotiable = 0;
            }
            $goods['is_negotiable'] = $is_negotiable;

            //批发相关处理
            CommonForm::getWholesalePrice($goods);
            if ($goods['price_section']) {
                $goods['price_min'] = $goods['price_section']['min_price'];
                $goods['price_max'] = $goods['price_section']['max_price'];
            }
            if ($goods['level_price_section']) {
                $goods['price_member_min'] = $goods['level_price_section']['min_level_price'];
                $goods['price_member_max'] = $goods['level_price_section']['max_level_price'];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $goods
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
