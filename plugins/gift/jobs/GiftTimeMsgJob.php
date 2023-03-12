<?php

/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author jack_guo
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 */


namespace app\plugins\gift\jobs;


use app\forms\common\template\TemplateList;
use app\models\Goods;
use app\models\Mall;
use app\plugins\gift\forms\common\CommonGift;
use app\plugins\gift\forms\common\GiftFormUserInfo;
use app\plugins\gift\forms\common\GiftToUserInfo;
use app\plugins\gift\models\GiftLog;
use app\plugins\gift\models\GiftOrder;
use app\plugins\gift\models\GiftSetting;
use app\plugins\gift\models\GiftUserOrder;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class GiftTimeMsgJob extends BaseObject implements JobInterface
{
    /** @var Mall $mall */
    public $mall;

    /** @var GiftLog $gift_log_info */
    public $gift_log_info;

    /***
     * @param Queue $queue
     * @return mixed|void
     * @throws \Exception
     */
    public function execute($queue)
    {
        \Yii::$app->setMall($this->mall);
        \Yii::error('通知队列开始gift_id：' . $this->gift_log_info->id);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $setting = GiftSetting::search();

            $gift_info = GiftUserOrder::find()->select(['uo.*', 'o.goods_id', 'o.order_no'])
                ->alias('uo')->leftJoin(['o' => GiftOrder::tableName()], 'o.user_order_id = uo.id')
                ->andWhere(['o.is_refund' => 0, 'uo.is_turn' => 0, 'uo.is_receive' => 0, 'uo.is_win' => 1, 'uo.is_delete' => 0, 'uo.gift_id' => $this->gift_log_info->id])
                ->with(['user', 'giftLog.user'])
                ->asArray()->all();
            if (empty($gift_info)) {
                throw new \Exception('没有需要通知的礼物订单');
            }
            \Yii::error(json_encode($gift_info));
            foreach ($gift_info as $item) {
                $goods = Goods::find()->where(['id' => $item['goods_id']])->with('goodsWarehouse')->one();
                if (empty($goods)) {
                    throw new \Exception('礼物商品信息错误');
                }
                $form_data = [
                    'order_no' => $item['order_no'],
                    'name' => $goods->getName(),
                    'user' => $item['giftLog']['user']
                ];
                $to_data = [
                    'time' => $this->gift_log_info->type == 'time_open'
                        ? date('Y-m-d H:i:s', strtotime($this->gift_log_info->auto_refund_time) + $setting['auto_refund'] * 86400)
                        : $this->gift_log_info->auto_refund_time,
                    'order_no' => $item['order_no'],
                    'name' => $goods->getName(),
                    'user' => $item['user']
                ];

                $this->sendTemplateFromUser($form_data);
                $this->sendTemplateToUser($to_data);
                
                if ($setting['is_sms'] == 1) {
                    \Yii::error('发送短信通知');
                    CommonGift::sendSms($item['giftLog']['user']['mobile'], 'gift');
                    CommonGift::sendSms($item['user']['mobile'], 'gift');
                    \Yii::error($item['giftLog']['user']['mobile']);
                    \Yii::error($item['user']['mobile']);
                }
            }
            $t->commit();
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error('礼物定时抽奖队列错误');
            \Yii::error($e->getMessage());
            \Yii::error($e);
            throw $e;
        }
    }

    // 送礼人消息
    public function sendTemplateFromUser($formData)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(GiftFormUserInfo::TPL_NAME)->send([
                'order_no' => $formData['order_no'],
                'name' => $formData['name'],
                'user' => $formData['user'],
                'page' => 'pages/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    // 收礼人消息
    public function sendTemplateToUser($toData)
    {
        try {
            TemplateList::getInstance()->getTemplateClass(GiftToUserInfo::TPL_NAME)->send([
                'order_no' => $toData['order_no'],
                'name' => $toData['name'],
                'time' => $toData['time'],
                'user' => $toData['user'],
                'page' => 'pages/index/index'
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }
}
