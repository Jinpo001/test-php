<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: jack_guo
 */

namespace app\plugins\community\forms\api;

use app\core\response\ApiCode;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\goods\GoodsAuth;
use app\helpers\ArrayHelper;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\plugins\community\forms\common\CommonActivity;
use app\plugins\community\forms\common\CommonSetting;
use app\plugins\community\models\CommunityActivity;
use app\plugins\community\models\CommunityAddress;
use app\plugins\community\models\CommunityGoods;
use app\plugins\community\models\CommunityLog;
use app\plugins\community\models\CommunityMiddleman;
use app\plugins\community\models\CommunityRelations;
use app\plugins\community\models\Goods;
use app\plugins\community\Plugin;
use yii\db\Exception;

/**
 * @property Mall $mall
 */
class UserActivityForm extends Model
{
    public $id;
    public $user_id;
    public $middleman_id;
    public $longitude;
    public $latitude;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id', 'middleman_id'], 'integer'],
            [['longitude', 'latitude'], 'number']
        ];
    }

    public function getActivityDetail()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $middlemanInfo = $this->getMiddlemanInfo();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => array_merge(
                    CommonActivity::getActivityDetail($this->id, $this->middleman_id, 1),
                    [
                        'middleman_info' => ArrayHelper::filter($middlemanInfo, [
                            'avatar', 'city', 'detail', 'distance', 'district', 'id', 'latitude', 'location',
                            'longitude', 'mobile', 'name', 'province', 'user_id', 'is_allow_change'
                        ]),
                        'is_middleman' => CommunityMiddleman::findOne([
                            'user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'status' => 1
                        ]) ? 1 : 0
                    ]
                )
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    protected function getMiddlemanInfo()
    {
        if (!$this->id) {
            throw new Exception('活动ID不能为空');
        }

        //不是通过分享，且未绑定团长，最近的团长
        if (!$this->longitude || !$this->latitude) {
            throw new Exception('手机位置未授权');
        }
        $setting = CommonSetting::getCommon()->getSetting();
        $relations = CommunityRelations::findOne(['user_id' => \Yii::$app->user->id, 'is_delete' => 0]);
        $model = CommunityMiddleman::find()->alias('m')
            ->leftJoin(['ca' => CommunityAddress::tableName()], 'ca.user_id = m.user_id')
            ->where([
                'm.mall_id' => \Yii::$app->mall->id, 'm.is_delete' => 0, 'm.status' => 1,
                'ca.is_delete' => 0, 'ca.is_default' => 1
            ])
            ->with('userInfo')->select('ca.*');
        if (empty($relations) || $relations->middleman_id == 0) {
            //用户分享，被分享的人显示分享人的团长
            if ($this->user_id) {
                $user_relations = CommunityRelations::findOne(['user_id' => $this->user_id, 'is_delete' => 0]);
                $this->middleman_id = $user_relations->middleman_id ?? 0;
            }
            //未绑定，非分享进入
            if (!$this->middleman_id) {
                $middlemanInfo = $model->asArray()->all();
                if (empty($middlemanInfo)) {
                    throw new Exception('附近没有团长');
                }
                $distance = 0;
                $info = [];
                foreach ($middlemanInfo as $item) {
                    $item['distance'] = get_distance($this->longitude, $this->latitude, $item['longitude'], $item['latitude']);
                    //取最近距离的
                    if ($item['distance'] < $distance || $distance == 0) {
                        $distance = $item['distance'];
                        $info = $item;
                    }
                }
                $middlemanInfo = $info;
                $this->middleman_id = $info['user_id'];
            } else {
                //未绑定，分享进入
                $middlemanInfo = $model->andWhere(['m.user_id' => $this->middleman_id])->asArray()->one();

            }
            $setting['is_allow_change'] = 1;//只要是没绑定团长的都是可以切换的
        } else {
            $this->middleman_id = $relations->middleman_id;
            //已绑定
            $middlemanInfo = $model->andWhere(['m.user_id' => $this->middleman_id])->asArray()->one();
        }
        $middlemanInfo['distance'] = get_distance($this->longitude, $this->latitude, $middlemanInfo['longitude'], $middlemanInfo['latitude']);
        $middlemanInfo['avatar'] = $middlemanInfo['userInfo']['avatar'];
        $middlemanInfo['is_allow_change'] = $setting['is_allow_change'];
        unset($middlemanInfo['userInfo']);
        //记录浏览
        $log = CommunityLog::findOne([
            'user_id' => \Yii::$app->user->id, 'middleman_id' => $this->middleman_id,
            'activity_id' => $this->id, 'is_delete' => 0
        ]);
        if (empty($log)) {
            $log = new CommunityLog();
            $log->user_id = \Yii::$app->user->id;
            $log->middleman_id = $this->middleman_id;
            $log->activity_id = $this->id;
            if (!$log->save()) {
                throw new Exception((new Model())->getErrorMsg($log));
            }
        }
        return $middlemanInfo;
    }

    public function getNewUserActivity()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        try {
            $middlemanInfo = $this->getMiddlemanInfo();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => array_merge(
                    CommonActivity::getNewActivityDetail($this->id, $this->middleman_id, 1),
                    [
                        'middleman_info' => ArrayHelper::filter($middlemanInfo, [
                            'avatar', 'city', 'detail', 'distance', 'district', 'id', 'latitude', 'location',
                            'longitude', 'mobile', 'name', 'province', 'user_id', 'is_allow_change'
                        ]),
                        'is_middleman' => CommunityMiddleman::findOne([
                            'user_id' => \Yii::$app->user->id, 'is_delete' => 0, 'status' => 1
                        ]) ? 1 : 0,
                        'recommend_activity' => $this->recommendActivity(),
                        'last_mobile' => Order::find()
                            ->andWhere(['user_id' => \Yii::$app->user->id, 'sign' => (new Plugin())->getName()])
                            ->limit(1)->orderBy('created_at desc')
                            ->select('mobile')->scalar() ?: '',
                        'template_message_list' => CommonActivity::getTemplateMessage(),
                    ]
                )
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
                'line' => $e->getLine()
            ];
        }
    }

    public function recommendActivity($ignore = [])
    {
        //推荐活动
        $model = CommunityActivity::find()->alias('ca')->where([
            'ca.mall_id' => \Yii::$app->mall->id,
            'ca.is_delete' => 0
        ])->andWhere(['not in', 'ca.id', $ignore]);
        $address = CommunityAddress::findOne([
            'user_id' => $this->middleman_id, 'is_default' => 1, 'is_delete' => 0
        ]);
        $model->andWhere([
            'or',
            ['ca.area_limit' => '0,'],
            ['like', 'ca.area_limit', ',' . $address->district_id . ','],
            ['like', 'ca.area_limit', ',' . $address->city_id . ','],
            ['like', 'ca.area_limit', ',' . $address->province_id . ',']
        ])
            ->andWhere(['<=', 'ca.start_at', mysql_timestamp()])
            ->andWhere(['>=', 'ca.end_at', mysql_timestamp()])
            ->andWhere(['ca.status' => 1])
            ->leftJoin(['cg' => CommunityGoods::tableName()], 'cg.activity_id = ca.id and cg.is_delete = 0')
            ->leftJoin(['g' => Goods::tableName()], 'g.id = cg.goods_id')->andWhere(['g.status' => 1]);

        $recommendInfo = $model->orderBy('start_at desc')->asArray()->one();

        $goodsList = [];
        if (!empty($recommendInfo)) {
            /** @var CommunityGoods[] $goods */
            $goods = CommunityGoods::find()->alias('cg')
                ->where(['cg.activity_id' => $recommendInfo['id'], 'cg.is_delete' => 0])
                ->leftJoin(['g' => Goods::tableName()], 'g.id = cg.goods_id')->andWhere(['g.status' => 1])
                ->andWhere(CommonGoodsList::showAuthCondition('g'))
                ->orderBy('g.sort')->limit(4)->with('goods')->all();

            foreach ($goods as $k => $goodsItem) {
                $goodsList[$k]['cover_pic'] = $goodsItem->goods->getCoverPic();
                if ($k >= 2) {
                    break;
                }
            }
            if (count($goodsList) == 0) {
                $ignore[] = $recommendInfo['id'];
                return $this->recommendActivity($ignore);
            }
        }
        return [
            'activity_id' => $recommendInfo['id'] ?? 0,
            'count' => count($goodsList) ?? 0,
            'goods_list' => $goodsList
        ];
    }
}
