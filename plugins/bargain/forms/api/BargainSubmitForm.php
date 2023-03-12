<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/14
 * Time: 10:55
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\forms\api;


use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\forms\common\goods\CommonBargainGoods;
use app\plugins\bargain\jobs\BargainOrderSubmitJob;
use app\plugins\bargain\models\BargainGoods;
use app\plugins\bargain\models\BargainOrder;
use yii\db\Exception;

/**
 * @property Mall $mall
 * @property User $user
 */
class BargainSubmitForm extends ApiModel
{

    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['goods_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '砍价活动ID',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            /* @var BargainGoods $bargainGoods */
            $bargainGoods = CommonBargainGoods::getCommonGoods($this->mall)->getGoods($this->goods_id);
            if (!$bargainGoods) {
                throw new Exception('砍价商品不存在');
            }
            if ($bargainGoods->goods->status == 0) {
                throw new Exception('砍价活动已关闭');
            }

            $limit_data = \yii\helpers\BaseJson::decode($bargainGoods->limit_data);
            if (!empty($limit_data)
                && $limit_data['limit_status']
                && $limit_data['limit_i_status'] === 'cycle'
            ) {
                $query = BargainOrder::find()->where([
                    'mall_id' => $this->mall->id,
                    'user_id' => $this->user->id,
                    'bargain_goods_id' => $bargainGoods->id,
                    'is_delete' => 0,
                ]);
                $title = '发起砍价';
                if ($start_time = CommonBargainGoods::calcTime($limit_data['limit_i_type'], $title, $sub_title)) {
                    $query->andWhere([
                        'AND',
                        ['<', 'created_at', date('Y-m-d H:i:s')],
                        ['>=', 'created_at', $start_time]
                    ]);
                }
                if ($query->count() >= $limit_data['limit_i_num']) {
                    $t->rollBack();
                    return [
                        'code' => ApiCode::CODE_ERROR,
                        'msg' => $title,
                        'data' => [
                            'error_type' => 'limit',
                            'title' => $title,
                            'sub_title' => $sub_title
                        ]
                    ];
                }
            }

            $beginTime = strtotime($bargainGoods->begin_time);
            $endTime = strtotime($bargainGoods->end_time);
            $nowDate = time();
            if ($beginTime >= $nowDate) {
                throw new Exception('砍价活动尚未开始');
            }
            if ($endTime <= $nowDate) {
                throw new Exception('砍价活动已结束');
            }
            $commonBargainOrder = CommonBargainOrder::getCommonBargainOrder($this->mall);

            /* @var BargainOrder $bargainOrder */
            $bargainOrder = $commonBargainOrder->getUserOrder($bargainGoods->id, $this->user->id);
            if ($bargainOrder && $bargainOrder->resetTime > 0) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '已存在进行中的砍价',
                    'data' => [
                        'token' => $bargainOrder->token
                    ]
                ];
            }

            /* @var Goods $goods */
            $goods = $bargainGoods->goods;

            /* @var BargainOrder[] $bargainOrderSuccess */
            $bargainOrderSuccess = $commonBargainOrder->getBargainOrderSuccess($bargainGoods->id, $this->user->id);
            if ($goods->confine_count > 0
                && $bargainOrderSuccess
                && count($bargainOrderSuccess) >= $goods->confine_count) {
                throw new Exception('已达砍价活动限制数量');
            }
            $token = \Yii::$app->security->generateRandomString();
            $queueId = \Yii::$app->queue
                ->delay(0)
                ->push(new BargainOrderSubmitJob([
                    'mall' => $this->mall,
                    'user' => $this->user,
                    'bargainGoods' => $bargainGoods,
                    'token' => $token
                ]));
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'queueId' => $queueId,
                    'token' => $token
                ]
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
