<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 16:41
 */

namespace app\forms\api\share;


use app\core\response\ApiCode;
use app\forms\common\CommonMallMember;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\mptemplate\MpTplMsgDSend;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\forms\common\share\CommonShare;
use app\forms\common\template\TemplateList;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PaymentOrder;
use app\models\Share;
use app\models\ShareSetting;
use app\models\User;

/**
 * @property User $user
 * @property Mall $mall
 */
class ShareApplyForm extends Model
{
    public $mall;
    public $user;

    public $name;
    public $mobile;
    public $agree;
    public $form;


    public function rules()
    {
        return [
            [['name', 'mobile', 'agree'], 'required'],
            [['name', 'mobile', 'form'], 'trim'],
            [['name', 'mobile', 'form'], 'string'],
            [['agree'], 'integer'],
            [['mobile'], 'app\validators\PhoneNumberValidator']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '申请人姓名',
            'mobile' => '申请人联系方式',
            'agree' => '阅读申请协议',
            'form' => '自定义表单',
        ];
    }

    /**
     * @return array
     */
    public function save()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->mall = \Yii::$app->mall;
            $this->user = \Yii::$app->user->identity;
            $shareCondition = ShareSetting::get($this->mall->id, ShareSetting::SHARE_CONDITION);
            $shareForm = ShareSetting::get($this->mall->id, ShareSetting::FORM_STATUS);
            if (!$this->checkApply()) {
                throw new \Exception('不满足条件无法申请');
            }
            $commonShare = CommonShare::getCommon();
            switch ($shareCondition) {
                case 1:
                    $attributes = [
                        'status' => 0,
                        'apply_at' => mysql_timestamp(),
                    ];
                    break;
                case 2:
                    if (!$this->validate()) {
                        return $this->getErrorResponse();
                    }
                    if ($this->agree == 0) {
                        throw new \Exception('请先查看分销协议并同意');
                    }
                    if ($shareForm == 1 && $this->form == null) {
                        throw new \Exception('请填写表单');
                    }
                    $attributes = [
                        'status' => 0,
                        'name' => $this->name,
                        'mobile' => $this->mobile,
                        'form' => $this->form,
                        'apply_at' => mysql_timestamp(),
                    ];
                    break;
                case 5:
                case 3:
                    $attributes = [
                        'status' => 1,
                        'apply_at' => mysql_timestamp(),
                    ];
                    break;
                case 4:
                    if (!$this->validate()) {
                        return $this->getErrorResponse();
                    }
                    if ($this->agree == 0) {
                        throw new \Exception('请先查看分销协议并同意');
                    }
                    if ($shareForm == 1 && $this->form == null) {
                        throw new \Exception('请填写表单');
                    }
                    $attributes = [
                        'status' => 1,
                        'name' => $this->name,
                        'mobile' => $this->mobile,
                        'form' => $this->form,
                        'apply_at' => mysql_timestamp(),
                    ];
                    break;
                default:
                    throw new \Exception('分销商基础设置未设置');
                    break;
            }
            try {
                $tplMsg = new MpTplMsgSend();
                $tplMsg->method = 'shareApplyTpl';
                $tplMsg->params = [
                    'time' => date('Y-m-d H:i:s'),
                    'content' => '申请已提交',
                ];
                $tplMsg->sendTemplate(new MpTplMsgDSend());
            } catch (\Exception $exception) {
                \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
            }

            $commonShare->becomeShare($this->user, $attributes);
            $t->commit();
            return [
                'code' => 0,
                'msg' => '申请分销商成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     * 旧版接口
     */
    public function getStatus()
    {
        $model = Share::findOne(['mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
        $templateMessage = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['audit_result_tpl']);
        if (!$model) {
            $model = [
                'status' => 2,
            ];
        }
        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => [
                'share' => $model,
                'template_message' => $templateMessage,
            ]
        ];
    }

    /**
     * @return array
     * status展示的状态 0--分销商申请中 1--已经是分销商 2--可以申请分销商 3--条件未满足 4--分销商申请被拒绝 5--分销商被删除 6--暂时不能申请分销商
     */
    public function getShareStatus()
    {
        $model = Share::findOne([
            'mall_id' => \Yii::$app->mall->id, 'user_id' => \Yii::$app->user->id
        ]);
        $templateMessage = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, ['audit_result_tpl',
            'remove_identity_tpl']);
        $isCanApply = false;
        if ($model && $model->is_delete == 0 && $model->status != 2) {
            $status = $model->status;
        } else {
            if (!$model) {
                $status = 3;
            } else {
                if ($model->delete_first_show == 0) {
                    if ($model->status == 1 && $model->is_delete == 1) {
                        $status = 5;
                    } else {
                        $status = 4;
                    }
                    $model->delete_first_show = 1;
                    $model->save();
                } else {
                    $status = 3;
                }
            }
            try {
                $shareLevel = ShareSetting::get($this->mall->id, ShareSetting::LEVEL, 0);
                if ($shareLevel == 0) {
                    $isCanApply = false;
                    $status = 6;
                } else {
                    $isCanApply = $this->checkApply();
                    if ($status == 3 && $isCanApply) {
                        $status = 2;
                    }
                }
            } catch (\Exception $exception) {
                $isCanApply = false;
            }
        }
        return [
            'code' => 0,
            'msg' => '数据请求成功',
            'data' => [
                'share' => $model,
                'status' => $status,
                'is_can_apply' => $isCanApply,
                'orderPrice' => $this->orderPrice,
                'goodsList' => $this->goodsList,
                'catList' => $this->catList,
                'template_message' => $templateMessage,
            ]
        ];
    }

    protected $orderPrice;
    protected $goodsList;
    protected $catList;

    /**
     * @return bool
     * @throws \Exception
     * 判断是否满足申请分销商的条件
     */
    public function checkApply()
    {
        $shareLevel = ShareSetting::get($this->mall->id, ShareSetting::LEVEL, 0);
        if ($shareLevel == 0) {
            return false;
        }
        $becomeCondition = ShareSetting::get($this->mall->id, ShareSetting::BECOME_CONDITION, 3);
        $default = $becomeCondition == 4 ? 2 : 1;
        $consumeCondition = ShareSetting::get($this->mall->id, ShareSetting::CONSUME_CONDITION, $default);
        switch (intval($becomeCondition)) {
            case 1:
                return $this->checkOrder($consumeCondition);
                break;
            case 2:
                switch ($consumeCondition) {
                    case 1:
                        return $this->checkGoods();
                        break;
                    case 2:
                        return $this->checkGoodsBySale();
                        break;
                    default:
                }
                break;
            case 3:
                return true;
                break;
            case 4:
                return $this->checkTotalOrder($consumeCondition);
                break;
            default:
        }
        return false;
    }

    /**
     * @param integer $consumeCondition
     * @return bool
     * @throws \Exception
     * 判断是否满足单次消费
     */
    protected function checkOrder($consumeCondition)
    {
        $autoShareVal = ShareSetting::get($this->mall->id, ShareSetting::AUTO_SHARE_VAL, 0);
        switch ($consumeCondition) {
            case 1:
                $this->orderPrice = CommonShare::getCommon($this->mall)->getConsume();
                break;
            case 2:
                $this->orderPrice = CommonShare::getCommon($this->mall)->getConsumeBySale();
                break;
            default:
                return false;
        }
        if ($autoShareVal > $this->orderPrice) {
            throw new \Exception('未满足单次消费' . $autoShareVal . '元的条件，不能申请分销商');
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     * 判断是否满足购买商品的条件(付款后)
     */
    protected function checkGoods()
    {
        $shareGoodsStatus = ShareSetting::get($this->mall->id, ShareSetting::SHARE_GOODS_STATUS, 1);
        switch (intval($shareGoodsStatus)) {
            case 1:
                // 购买任意商品
                $order = Order::find()->where([
                    'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_pay' => 1
                ])->exists();
                if (!$order) {
                    throw new \Exception('未购买任意商品无法申请分销商');
                }
                break;
            case 2:
                // 购买指定商品
                $goodsWarehouseId = ShareSetting::get($this->mall->id, ShareSetting::SHARE_GOODS_WAREHOUSE_ID);
                if (!$goodsWarehouseId || empty($goodsWarehouseId)) {
                    throw new \Exception('未购买指定商品无法申请分销商');
                }
                $orderQuery = Order::find()->where([
                    'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_pay' => 1
                ])->select('id');
                $goodsQuery = Goods::find()->where([
                    'is_delete' => 0, 'mall_id' => $this->mall->id, 'goods_warehouse_id' => $goodsWarehouseId
                ])->select('id');
                $orderDetail = OrderDetail::find()->where([
                    'order_id' => $orderQuery, 'goods_id' => $goodsQuery
                ])->exists();
                $this->getGoodsList($goodsWarehouseId);
                if (!$orderDetail) {
                    throw new \Exception('未购买指定商品无法申请分销商');
                }
                break;
            case 3:
                // 购买指定分类商品
                $catIdList = ShareSetting::get($this->mall->id, ShareSetting::CAT_LIST);
                if (!$catIdList || empty($catIdList)) {
                    throw new \Exception('未购买指定分类无法申请分销商');
                }
                $goodsWarehouseId = GoodsCatRelation::find()->where([
                    'cat_id' => $catIdList, 'is_delete' => 0,
                ])->select('goods_warehouse_id');
                $orderQuery = Order::find()->where([
                    'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_pay' => 1
                ])->select('order_id');
                $goodsQuery = Goods::find()->where([
                    'is_delete' => 0, 'mall_id' => $this->mall->id, 'goods_warehouse_id' => $goodsWarehouseId
                ])->select('id');
                $orderDetail = OrderDetail::find()->where([
                    'order_id' => $orderQuery, 'goods_id' => $goodsQuery
                ])->exists();
                $this->getCatList($catIdList);
                if (!$orderDetail) {
                    throw new \Exception('未购买指定商品无法申请分销商');
                }
                break;
            default:
                throw new \Exception('未知错误，请联系管理员');
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     * 判断是否满足购买商品的条件(过售后)
     */
    protected function checkGoodsBySale()
    {
        $shareGoodsStatus = ShareSetting::get($this->mall->id, ShareSetting::SHARE_GOODS_STATUS, 1);
        switch (intval($shareGoodsStatus)) {
            case 1:
                // 购买任意商品
                $order = Order::find()->where([
                    'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_sale' => 1
                ])->exists();
                if (!$order) {
                    throw new \Exception('未购买任意商品无法申请分销商');
                }
                break;
            case 2:
                // 购买指定商品
                $goodsWarehouseId = ShareSetting::get($this->mall->id, ShareSetting::SHARE_GOODS_WAREHOUSE_ID);
                if (!$goodsWarehouseId || empty($goodsWarehouseId)) {
                    throw new \Exception('未购买指定商品无法申请分销商');
                }
                $orderQuery = Order::find()->where([
                    'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_sale' => 1
                ])->select('id');
                $goodsQuery = Goods::find()->where([
                    'is_delete' => 0, 'mall_id' => $this->mall->id, 'goods_warehouse_id' => $goodsWarehouseId
                ])->select('id');
                $orderDetail = OrderDetail::find()->where([
                    'order_id' => $orderQuery, 'goods_id' => $goodsQuery
                ])->exists();
                $this->getGoodsList($goodsWarehouseId);
                if (!$orderDetail) {
                    throw new \Exception('未购买指定商品无法申请分销商');
                }
                break;
            case 3:
                // 购买指定分类商品
                $catIdList = ShareSetting::get($this->mall->id, ShareSetting::CAT_LIST);
                if (!$catIdList || empty($catIdList)) {
                    throw new \Exception('未购买指定分类无法申请分销商');
                }
                $goodsWarehouseId = GoodsCatRelation::find()->where([
                    'cat_id' => $catIdList, 'is_delete' => 0,
                ])->select('goods_warehouse_id');
                $orderQuery = Order::find()->where([
                    'mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id, 'is_sale' => 1
                ])->select('order_id');
                $goodsQuery = Goods::find()->where([
                    'is_delete' => 0, 'mall_id' => $this->mall->id, 'goods_warehouse_id' => $goodsWarehouseId
                ])->select('id');
                $orderDetail = OrderDetail::find()->where([
                    'order_id' => $orderQuery, 'goods_id' => $goodsQuery
                ])->exists();
                $this->getCatList($catIdList);
                if (!$orderDetail) {
                    throw new \Exception('未购买指定商品无法申请分销商');
                }
                break;
            default:
                throw new \Exception('未知错误，请联系管理员');
        }
        return true;
    }

    public function getGoodsList($goodsWarehouseId)
    {
        $form = new CommonGoodsList();
        $form->goodsWarehouseId = $goodsWarehouseId;
        $form->status = 1;
        $form->is_show = 1;
        $form->is_array = true;
        $form->sign = ['mch', ''];
        $form->is_sales = $this->mall->getMallSettingOne('is_sales');
        $form->relations = ['goodsWarehouse', 'mallGoods'];
        $form->limit = 20;
        $this->goodsList = $form->getList();
    }

    public function getCatList($catIdList)
    {
        $catList = GoodsCats::findAll([
            'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id,
            'id' => $catIdList
        ]);
        $this->catList = [];
        if ($catList) {
            foreach ($catList as $cat) {
                $this->catList[] = [
                    'label' => $cat['name'],
                    'value' => $cat['id']
                ];
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     * 判断是否满足累计消费
     */
    protected function checkTotalOrder($consumeCondition)
    {
        $totalConsume = ShareSetting::get($this->mall->id, ShareSetting::TOTAL_CONSUME, 0);
        // 订单总额
        switch ($consumeCondition) {
            case 1:
                $this->orderPrice = CommonShare::getCommon($this->mall)->getTotalConsume();
                break;
            case 2:
                $commonMallMember = new CommonMallMember();
                $mallId = $this->mall->id;
                $userId = \Yii::$app->user->id;
                $orderPrice = $commonMallMember->getOrderMoneyCount($mallId, $userId);
                $this->orderPrice = round($orderPrice, 2);
                break;
            default:
                return false;
        }
        if ($totalConsume > $this->orderPrice) {
            throw new \Exception('未满足累计消费' . $totalConsume . '元的条件，不能申请分销商');
        }
        return true;
    }
}
