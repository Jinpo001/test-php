<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\core\response\ApiCode;
use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\plugins\teller\forms\common\CommonTellerSetting;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerOrders;
use app\plugins\teller\models\TellerSales;
use app\plugins\teller\models\TellerWorkLog;

class ManageIndexForm extends Model
{
    private $cashier;

    public function rules()
    {
        return [];
    }

    //GET
    public function search()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        };

        try {
            $setting = (new CommonTellerSetting())->search();
            $cashier = $this->getCashier();
            $mall = $this->getMall();
            $mall['mall_id'] = base64_encode($mall['id']);
            $goods = $this->getGoods($setting['goods_id']);
            $sales = $this->getSales();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'cashier'=> $cashier,
                    'mall' => $mall,
                    'tab_list' => $setting['is_tab'] ? $setting['new_tab_list'] : [],
                    'goods' => $goods,
                    'setting' => $setting,
                    'sales' => $sales
                ],
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function getMall()
    {
        $logo = (new Mall())->getMallSettingOne('mall_logo_pic');
        $array = [
            'name' => \Yii::$app->mall->name,
            'id' => \Yii::$app->mall->id,
            'logo' => $logo,
            'store_name' => $this->cashier->store->name
        ];

        return $array;
    }

    private function getCashier()
    {
        $this->cashier = $cashier = TellerCashier::find()->andWhere([
            'user_id' => \Yii::$app->user->id
        ])->with('user', 'store')->one();

        if (!$cashier->user) {
            throw new \Exception('收银员账号异常');
        }

        if (!$cashier->store) {
            throw new \Exception('收银员所属门店异常');
        }

        return [
            'number' => $cashier->number,
            'nickname' => $cashier->user->nickname,
            'username' => $cashier->user->username,
            'mobile' => $cashier->user->mobile,
            'store_name' => $cashier->store->name,
        ];
    }

    private function getGoods($goodsId)
    {
        $goods = Goods::find()->where([
            'is_delete' => 0,
            'mall_id' => \Yii::$app->mall->id,
            'id' => $goodsId,
        ])
            ->with(['attr', 'goodsWarehouse'])
            ->one();

        $data = [
            'id' => $goods->id,
            'name' => $goods->goodsWarehouse->name,
            'cover_pic' => $goods->goodsWarehouse->cover_pic,
            'price' => $goods->price,
            'attr_groups' => json_decode($goods->attr_groups, true),
            'attr' => $goods->attr
        ];

        return $data;
    }

    private function getSales()
    {
        $sales = TellerSales::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'status' => 1,
            'is_delete' => 0,
        ])->with('store')->all();

        $list = array_map(function($item) {
            return [
                'id' => $item->id,
                'number' => $item->number,
                'name' => $item->name,
                'mobile' => $item->mobile,
                'store_name' => $item->store->name,
                'head_url' => $item->head_url
            ];
        }, $sales);

        return $list;
    }

    // 交班
    public function offDuty()
    {
        try {
            $setting = (new CommonTellerSetting())->search();

            if (!$setting['is_shifts']) {
                throw new \Exception('交班系统未开启,无法交班');
            }

            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'user_id' => \Yii::$app->user->id
            ])->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            $workLog = TellerWorkLog::find()->andWhere([
                'mall_id' => $cashier->mall_id,
                'mch_id' => $cashier->mch_id,
                'cashier_id' => $cashier->id,
                'is_delete' => 0,
                'status' => TellerWorkLog::PENDING
            ])->one();

            if (!$workLog) {
                throw new \Exception('无上班记录,交班异常');
            }

            $workLog->status = TellerWorkLog::FINISH;
            $workLog->end_time = date('Y-m-d H:i:s' , time());
            $res = $workLog->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($workLog));
            }

            $logout = \Yii::$app->user->logout();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'url' => 'plugin/teller/web/passport/login',
                    'mall_id' => base64_encode($workLog->mall_id)
                ],
                'msg' => '交班成功'
            ];

        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    public function getWorkLog()
    {
        try {
            $cashier = TellerCashier::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'user_id' => \Yii::$app->user->id
            ])->with('user')->one();

            if (!$cashier) {
                throw new \Exception('收银员不存在');
            }

            $workLog = TellerWorkLog::find()->andWhere([
                'mall_id' => $cashier->mall_id,
                'mch_id' => $cashier->mch_id,
                'cashier_id' => $cashier->id,
                'is_delete' => 0,
                'status' => TellerWorkLog::PENDING
            ])->one();

            if (!$workLog) {
                throw new \Exception('无上班记录');
            }

            $endTime = date('Y-m-d H:i:s', time());
            $cashierInfo = [
                'name' => $cashier->user->nickname,
                'number' => $cashier->number,
                'start_time' => $workLog->start_time,
                'end_time' => $endTime,
                'hour' => $this->getTimeCount($workLog->start_time, $endTime)
            ];

            $extra = json_decode($workLog->extra_attributes, true);
            $data = $workLog->getStatisticsData($workLog);
            $extra = array_merge($extra, $data);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'cashier_info' => $cashierInfo,
                    'order_info' => $extra
                ]
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }


    private function getTimeCount($startTime, $endTime)
    {
        $seconds = strtotime($endTime) - strtotime($startTime);

        if ($seconds > 3600) {
            $hour = intval($seconds/3600);
            $minutes = intval(($seconds % 3600) / 60);
            return sprintf('%s小时%s分钟', $hour, $minutes);
        } else {
            $minutes = intval(($seconds % 3600) / 60);
            $minutes = $minutes ?: 1;
            return sprintf('%s分钟', $minutes);
        }
    }

}
