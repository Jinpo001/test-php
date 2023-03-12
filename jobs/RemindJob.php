<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/7
 * Time: 4:14 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;


use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\order_pay_template\TailMoneyInfo;
use app\forms\common\template\TemplateList;
use app\models\Goods;
use app\models\GoodsRemind;
use app\models\Mall;
use yii\queue\JobInterface;

class RemindJob extends BaseJob implements JobInterface
{
    /**
     * @var Goods
     */
    public $goods;

    /**
     * @var Mall $mall
     */
    public $mall;

    /**
     * @var integer $user_id
     */
    public $user_id;
    public $sell_start_time;

    public function execute($queue)
    {
        try {
            $this->setRequest();
            $mall = Mall::findOne($this->mall->id);
            \Yii::$app->setMall($mall);
            $isRemindSellTime = \Yii::$app->mall->getMallSettingOne('is_remind_sell_time');
            if (!$isRemindSellTime) {
                return true;
            }
            $goods = Goods::findOne([
                'is_delete' => 0,
                'status' => 1,
                'id' => $this->goods->id,
                'mall_id' => \Yii::$app->mall->id
            ]);
            if (!$goods) {
                throw new \Exception('商品不存在或已下架');
            }
            if (strtotime($this->sell_start_time) !== strtotime($goods->sell_begin_time)) {
                throw new \Exception('商品销售时间有更改，此次不做提醒');
            }
            $remindList = GoodsRemind::find()->with('user')
                ->where([
                    'goods_id' => $this->goods->id, 'mall_id' => \Yii::$app->mall->id, 'is_remind' => 1,
                    'is_delete' => 0
                ])->all();
            /** @var GoodsRemind[] $remindList */
            foreach ($remindList as $remind) {
                if ($this->user_id && $remind->user_id != $this->user_id) {
                    continue;
                }
                if (
                    $remind->remind_at
                    && $remind->remind_at != '0000-00-00 00:00:00'
                    && strtotime($remind->remind_at) + 5 * 60 >= time()
                ) {
                    continue;
                }
                $remind->is_remind = 0;
                $remind->remind_at = mysql_timestamp();
                if (!$remind->save()) {
                    \Yii::warning($remind);
                    continue;
                }
                $this->sendTemplate($remind->user);
                $this->sendSmsToUser($remind->user);
            }
            return true;
        } catch (\Exception $exception) {
            \Yii::warning('--商品开售提醒' . $this->goods->id . '--');
            \Yii::warning($exception);
        }
    }

    protected function sendTemplate($user)
    {
        try {
            try {
                if ($this->goods->sign !== '') {
                    $plugin = \Yii::$app->plugin->getPlugin($this->goods->sign);
                    $pageUrl = $plugin->getGoodsUrl($this->goods);
                } else {
                    $pageUrl = sprintf("/pages/goods/goods?id=%u", $this->goods->id);
                }
            } catch (\Exception $exception) {
                $pageUrl = sprintf("/pages/goods/goods?id=%u", $this->goods->id);
            }
            TemplateList::getInstance()->getTemplateClass(TailMoneyInfo::TPL_NAME)->send([
                'desc' => '您设置提醒的商品即将开售，请尽快前往购买',
                'goodsName' => $this->goods->name,
                'price' => $this->goods->price,
                'time' => $this->goods->sell_end_time,
                'user' => $user,
                'page' => ltrim($pageUrl, '/')
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }
    protected function sendSmsToUser($user)
    {
        try {
            \Yii::warning('----消息发送提醒----');
            if (!$user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $user;
            $name = mb_substr($this->goods->name, 0, 20);
            if (mb_strlen($this->goods->name) > 20) {
                $name = mb_substr($this->goods->name, 0, 17) . '...';
            }
            $messageService->content = [
                'mch_id' => 0,
                'args' => [$name]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = 'tailMoney';
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}