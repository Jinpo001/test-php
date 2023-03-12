<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/19
 * Time: 15:52
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\bargain\jobs;


use app\forms\common\ecard\CommonEcard;
use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\forms\common\template\TemplateList;
use app\jobs\BaseJob;
use app\models\CoreExceptionLog;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use app\plugins\bargain\forms\common\BargainFailInfo;
use app\plugins\bargain\forms\common\CommonBargainOrder;
use app\plugins\bargain\models\BargainOrder;
use app\plugins\bargain\models\Code;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\JobInterface;

/**
 * @property BargainOrder $bargainOrder
 */
class BargainOrderTimeJob extends BaseJob implements JobInterface
{
    public $bargainOrder;

    public function execute($queue)
    {
        $this->setRequest();
        $t = \Yii::$app->db->beginTransaction();
        try {
            $mall = Mall::findOne(['id' => $this->bargainOrder->mall_id]);
            \Yii::$app->setMall($mall);
            $this->bargainOrder = CommonBargainOrder::getCommonBargainOrder()->getBargainOrder($this->bargainOrder->id);
            if ($this->bargainOrder->resetTime > 0) {
                \Yii::$app->queue->delay($this->bargainOrder->resetTime)->push(new BargainOrderTimeJob([
                    'bargainOrder' => $this->bargainOrder
                ]));
            }
            if ($this->bargainOrder->status == Code::BARGAIN_FAIL) {
                return false;
            }
            if ($this->bargainOrder->order) {
                if ($this->bargainOrder->status != Code::BARGAIN_SUCCESS) {
                    $this->bargainOrder->status = Code::BARGAIN_SUCCESS;
                } else {
                    return false;
                }
            } else {
                $this->bargainOrder->status = Code::BARGAIN_FAIL;
            }

            $baseModel = new Model();
            if (!$this->bargainOrder->save()) {
                throw new \Exception($baseModel->getErrorMsg($this->bargainOrder));
            }

            if ($this->bargainOrder->status == Code::BARGAIN_FAIL) {
                $bargainGoods = $this->bargainOrder->bargainGoods;
                $bargainGoodsData = Json::decode($this->bargainOrder->bargain_goods_data, true);
                if (!isset($bargainGoodsData['stock_type']) || $bargainGoodsData['stock_type'] == 1) {
                    $bargainGoods->stock += 1;
                    if ($bargainGoods->goodsWarehouse->type === 'ecard') {
                        CommonEcard::getCommon()->refundEcard([
                            'type' => 'occupy',
                            'sign' => 'bargain',
                            'num' => 1,
                            'goods_id' => $bargainGoods->goods_id,
                        ]);
                    }
                }
                $bargainGoods->fail += 1;
                $bargainGoods->underway -= min($bargainGoods->underway, 1);
                if (!$bargainGoods->save()) {
                    throw new \Exception($baseModel->getErrorMsg($bargainGoods));
                }
            }

            $this->sendTemplate($this->bargainOrder);
            $this->sendSmsToUser($this->bargainOrder->user);

            $t->commit();
        } catch (\Exception $exception) {
            $t->rollBack();
            \Yii::$app->queue->delay(0)->push(new BargainOrderTimeJob([
                'bargainOrder' => $this->bargainOrder
            ]));
            $form = new CoreExceptionLog();
            $form->mall_id = $this->bargainOrder->mall_id;
            $form->level = 1;
            $form->title = '砍价订单超时取消';
            $form->content = $exception->getMessage();
            $form->save();
        }
    }

    public function sendTemplate($bargainOrder)
    {
        try {
            $user = User::findOne(['id' => $bargainOrder->user_id]);
            $pageUrl = 'plugins/bargain/order-list/order-list';

            TemplateList::getInstance()->getTemplateClass(BargainFailInfo::TPL_NAME)->send([
                'goodsName' => $bargainOrder->goodsWarehouse->name,
                'price' => $bargainOrder->price . '元',
                'minPrice' => $bargainOrder->min_price . '元',
                'remark' => '超出砍价时间',
                'user' => $user,
                'page' => $pageUrl
            ]);
        } catch (\Exception $exception) {
            \Yii::error('模板消息发送: ' . $exception->getMessage());
        }
    }

    /**
     * @param User $user
     * @return $this
     * 向用户发送短信提醒
     */
    protected function sendSmsToUser($user)
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
                'args' => []
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($user);
            $messageService->tplKey = BargainFailInfo::TPL_NAME;
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
        return $this;
    }
}
