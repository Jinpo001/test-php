<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\mch\forms\mall;

use app\core\response\ApiCode;
use app\forms\common\platform\PlatformConfig;
use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\DistrictArr;
use app\models\Model;
use app\models\Order;
use app\models\User;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\models\Goods;
use app\plugins\mch\models\Mch;

class MchForm extends Model
{
    public $keyword;
    public $page;
    public $id;
    public $switch_type;
    public $password;
    public $sort;

    public $status = -1;

    public function rules()
    {
        return [
            [['keyword', 'switch_type', 'password'], 'string'],
            [['id', 'sort', 'status'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['sort'], 'integer', 'max' => 999999999]
        ];
    }

    public function attributeLabels()
    {
        return [
            'sort' => '排序'
        ];
    }

    public function getList()
    {
        $form = new CommonMchForm();
        $form->keyword = $this->keyword;
        $form->status = $this->status;
        $res = $form->getList();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $res['list'],
                'pagination' => $res['pagination']
            ]
        ];
    }

    public function getDetail()
    {
        try {
            $detail = Mch::find()->where([
                'id' => \Yii::$app->user->identity->mch_id ?: $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('user.userInfo', 'mchUser', 'store', 'category')->asArray()->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['latitude_longitude'] = $detail['store']['longitude'] && $detail['store']['latitude'] ?
                $detail['store']['latitude'] . ',' . $detail['store']['longitude'] : '';
            $detail['address'] = $detail['store']['address'];
            $detail['logo'] = $detail['store']['cover_url'];
            $detail['service_mobile'] = $detail['store']['mobile'];
            $detail['bg_pic_url'] = \Yii::$app->serializer->decode($detail['store']['pic_url']);
            $detail['name'] = $detail['store']['name'];
            $detail['description'] = $detail['store']['description'];
            $detail['scope'] = $detail['store']['scope'];
            $detail['district'] = [
                (int)$detail['store']['province_id'],
                (int)$detail['store']['city_id'],
                (int)$detail['store']['district_id']
            ];
            try {
                $detail['districts'] = DistrictArr::getDistrict((int)$detail['store']['province_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['city_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['district_id'])['name'];
            } catch (\Exception $e) {
                $detail['districts'] = '';
            }
            $detail['cat_name'] = $detail['category']['name'];
            $detail['form_data'] = $detail['form_data'] ? \Yii::$app->serializer->decode($detail['form_data']) : [];
            $detail['username'] = $detail['mchUser']['username'];
            $detail['password'] = $detail['mchUser']['password'];

            $data = json_decode($detail['store']['extra_attributes'], true);
            $detail['is_open'] = $data['is_open'] ? (int)$data['is_open'] : 1;
            $detail['open_type'] = $data['open_type'] ? (int)$data['open_type'] : 1;
            $detail['week_list'] = $data['week_list'] ?: [];
            $detail['time_list'] = $data['time_list'] ?: [];
            $detail['is_auto_open'] = $data['is_auto_open'] ? (int)$data['is_auto_open'] : 1;
            $detail['auto_open_time'] = $data['auto_open_time'] ?: '';

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var Mch $model */
            $model = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$model) {
                throw new \Exception('商户不存在');
            }

            $model->is_delete = 1;
            $res = $model->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($model));
            }

            /** @var User $user */
            $user = User::find()->where(['mch_id' => $model->id])->one();
            if (!$user) {
                throw new \Exception('商户账号不存在');
            }
            $user->is_delete = 1;
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            Goods::updateAll(['is_delete' => 1], ['mch_id' => $model->id]);
            Order::updateAll(['is_delete' => 1], ['mch_id' => $model->id]);
            $clerkUsers = ClerkUser::find()->where(['mch_id' => $model->id, 'is_delete' => 0])->select('id')->all();
            $ids = [];
            foreach ($clerkUsers as $clerkUser) {
                $ids[] = $clerkUser->id;
            }
            ClerkUserStoreRelation::updateAll(['is_delete' => 1], ['clerk_user_id' => $ids]);
            ClerkUser::updateAll(['is_delete' => 1], ['mch_id' => $model->id, 'is_delete' => 0]);

            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function switchStatus()
    {
        try {
            /** @var Mch $detail */
            $detail = Mch::find()->where([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            if ($this->switch_type == 'status') {
                $detail->status = $detail->status ? 0 : 1;
            }
            if ($this->switch_type == 'is_recommend') {
                $detail->is_recommend = $detail->is_recommend ? 0 : 1;
            }
            $res = $detail->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($detail));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function route()
    {
        $mallId = base64_encode(\Yii::$app->mall->id);
        $url = \Yii::$app->urlManager->createAbsoluteUrl('admin/passport/mch-login&mall_id=' . $mallId);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'url' => urldecode($url),
            ]
        ];
    }

    public function updatePassword()
    {
        try {
            if (!$this->password) {
                throw new \Exception('请填写新密码');
            }
            $user = User::find()->where([
                'mch_id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            if (!$user) {
                throw new \Exception('商户账号不存在');
            }

            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $res = $user->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($user));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '密码更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function searchUser()
    {
        $keyword = trim($this->keyword);
        $query = User::find()->alias('u')->select('u.id,u.nickname')->where([
            'AND',
            ['or', ['LIKE', 'u.nickname', $keyword], ['u.id' => $keyword]],
            ['u.mall_id' => \Yii::$app->mall->id],
        ]);

        $userIds = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
        ])
            ->andWhere(['in', 'review_status', [0, 1]])
            ->select('user_id');
        if ($userIds) {
            $query->andWhere(['not in', 'u.id', $userIds]);
        }

        $list = $query->InnerJoinwith('userInfo')->orderBy('nickname')->limit(10)->all();

        $newList = [];
        /** @var User[] $list */
        foreach ($list as $k => $v) {
            $newList[] = [
                'id' => $v->id,
                'nickname' => $v->nickname,
                'avatar' => $v->userInfo->avatar,
                'platform_icon' => PlatformConfig::getInstance()->getPlatformIcon($v)
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $newList,
            ]
        ];
    }

    public function editSort()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        
        try {
            $mch = Mch::findOne($this->id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            $mch->sort = $this->sort;
            $res = $mch->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($mch));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function getCount()
    {
        $count = Mch::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'review_status' => 0,
        ])->count();

        return $count;
    }
}
