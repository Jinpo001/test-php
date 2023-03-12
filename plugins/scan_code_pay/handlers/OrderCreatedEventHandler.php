<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\scan_code_pay\handlers;

use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\order_pay_template\AccountChangeInfo;
use app\forms\common\template\TemplateList;
use app\handlers\orderHandler\BaseOrderCreatedHandler;
use app\jobs\ChangeShareOrderJob;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Share;
use app\models\ShareOrder;
use app\models\ShareSetting;
use app\models\User;
use app\plugins\scan_code_pay\forms\common\CommonScanCodePaySetting;

class OrderCreatedEventHandler extends BaseOrderCreatedHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->setShareUser()->setShareMoney();
    }

    protected function saveShareMoney()
    {
        $scanCodePayShareSetting = (new CommonScanCodePaySetting())->getSetting();
        if (!$scanCodePayShareSetting['is_share']) {
            return;
        }
        parent::saveShareMoney();
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function saveShare($order)
    {
        $baseModel = new Model();
        $shareSetting = ShareSetting::getList($order->mall_id);
        if (!$shareSetting[ShareSetting::LEVEL] || $shareSetting[ShareSetting::LEVEL] < 1) {
            return;
        }

        $scanCodePayShareSetting = (new CommonScanCodePaySetting())->getSetting();
        if (!$scanCodePayShareSetting['is_share']) {
            return;
        }

        $user = User::findOne(['id' => $order->user_id]);
        if (!$user) {
            return;
        }
        $userInfo = $user->userInfo;
        if (!$userInfo) {
            return;
        }

        // 查询出3个级别的用户
        if ($shareSetting[ShareSetting::IS_REBATE] == 1 && $user->share && $user->share->status == 1
            && $user->identity->is_distributor == 1
            && $user->share->is_delete == 0) {
            // 自购返利 下单用户必须是分销商
            $firstParentUser = $user->share;
        } else {
            $firstParentUser = Share::findOne(['user_id' => $userInfo->parent_id, 'status' => 1, 'is_delete' => 0]);
        }
        if (!$firstParentUser) {
            return;
        }

        if ($firstParentUser && $firstParentUser->userInfo
            && $firstParentUser->userInfo->parent_id && $shareSetting[ShareSetting::LEVEL] > 1) {
            $secondParentUser = Share::findOne([
                'user_id' => $firstParentUser->userInfo->parent_id, 'is_delete' => 0, 'status' => 1
            ]);
        } else {
            $secondParentUser = null;
        }

        if ($secondParentUser && $secondParentUser->userInfo
            && $secondParentUser->userInfo->parent_id && $shareSetting[ShareSetting::LEVEL] > 2) {
            $thirdParentUser = Share::findOne([
                'user_id' => $secondParentUser->userInfo->parent_id, 'is_delete' => 0, 'status' => 1
            ]);
        } else {
            $thirdParentUser = null;
        }

        /** @var OrderDetail[] $orderDetails */
        $orderDetails = $order->getDetail()->with(['goods' => function ($query) {
            $query->with(['share']);
        }])->andWhere(['is_delete' => 0])->all();
        $shareOrderList = ShareOrder::find()->andWhere([
            'mall_id' => $order->mall_id,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'is_delete' => 0,
        ])->all();
        $firstPrice = 0;
        $secondPrice = 0;
        $thirdPrice = 0;
        foreach ($orderDetails as $orderDetail) {
            $first = 0;
            $second = 0;
            $third = 0;

            $firstValue = $scanCodePayShareSetting['share_commission_first'];
            if (!empty($firstValue) && is_numeric($firstValue)) {
                $first = $firstValue;
            }

            $secondValue = $scanCodePayShareSetting['share_commission_second'];
            if (!empty($secondValue) && is_numeric($secondValue)) {
                $second = $secondValue;
            }

            $thirdValue = $scanCodePayShareSetting['share_commission_third'];
            if (!empty($thirdValue) && is_numeric($thirdValue)) {
                $third = $thirdValue;
            }

            if ($scanCodePayShareSetting['share_type'] == 1) {
                $first = $first * $orderDetail->total_price / 100;
                $second = $second * $orderDetail->total_price / 100;
                $third = $third * $orderDetail->total_price / 100;
            } else {
                $first = $first * $orderDetail->num;
                $second = $second * $orderDetail->num;
                $third = $third * $orderDetail->num;
            }

            $model = ShareOrder::findOne([
                'mall_id' => $order->mall_id,
                'order_id' => $order->id,
                'order_detail_id' => $orderDetail->id,
                'user_id' => $order->user_id,
                'is_delete' => 0,
            ]);
            if (!$model) {
                $model = new ShareOrder();
                $model->mall_id = $order->mall_id;
                $model->order_id = $order->id;
                $model->user_id = $order->user_id;
                $model->order_detail_id = $orderDetail->id;
            }

            if ($firstParentUser) {
                $firstParentId = $firstParentUser->user_id;
            } else {
                $firstParentId = 0;
                $first = 0;
            }
            $model->first_parent_id = $firstParentId;
            $model->first_price = price_format($first);

            if ($secondParentUser) {
                $secondParentId = $secondParentUser->user_id;
            } else {
                $secondParentId = 0;
                $second = 0;
            }
            $model->second_parent_id = $secondParentId;
            $model->second_price = price_format($second);

            if ($thirdParentUser) {
                $thirdParentId = $thirdParentUser->user_id;
            } else {
                $thirdParentId = 0;
                $third = 0;
            }
            $model->third_parent_id = $thirdParentId;
            $model->third_price = price_format($third);

            $before = $model->oldAttributes;
            if (!$model->save()) {
                throw new \Exception($baseModel->getErrorMsg($model));
            }
            $firstPrice += $first;
            $secondPrice += $second;
            $thirdPrice += $third;
        }
        $firstPrice = price_format($firstPrice);
        $secondPrice = price_format($secondPrice);
        $thirdPrice = price_format($thirdPrice);
        if (count($shareOrderList) > 0) {
            try {
                if ($firstPrice > 0) {
                    $desc = '有用户下单，预计可得佣金' . $firstPrice;
                    $this->sendTemplate($firstParentUser->user, $desc);
                    $this->sendSmsToUser($firstParentUser->user, $user->nickname, $firstPrice);
                }
                if ($secondPrice > 0) {
                    $desc = '有用户下单，预计可得佣金' . $secondPrice;
                    $this->sendTemplate($secondParentUser->user, $desc);
                    $this->sendSmsToUser($secondParentUser->user, $user->nickname, $secondPrice);
                }
                if ($thirdPrice > 0) {
                    $desc = '有用户下单，预计可得佣金' . $thirdPrice;
                    $this->sendTemplate($thirdParentUser->user, $desc);
                    $this->sendSmsToUser($thirdParentUser->user, $user->nickname, $thirdPrice);
                }
            } catch (\Exception $exception) {
                \Yii::error('预计可得佣金发放');
                \Yii::error($exception);
            }
        }
        \Yii::$app->queue->delay(0)->push(new ChangeShareOrderJob([
            'mall' => \Yii::$app->mall,
            'order' => $order,
            'beforeList' => $shareOrderList,
            'type' => 'add'
        ]));
    }

    public function sendTemplate($user, $desc)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(AccountChangeInfo::TPL_NAME)->send([
                'remark' => '分销佣金',
                'desc' => $desc,
                'user' => $user,
                'page' => 'pages/user-center/user-center'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param User $user
     * @param $money
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($user, $nickname, $money)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => [$nickname, $money]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = 'brokerage';
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}