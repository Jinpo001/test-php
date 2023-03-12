<?php

namespace app\plugins\fission\jobs;

use app\jobs\BaseJob;
use app\models\Mall;
use app\models\User;
use app\plugins\fission\forms\receive\core\Create;
use app\plugins\fission\forms\receive\core\Reward;
use app\plugins\fission\forms\receive\exception\ConvertException;
use app\plugins\fission\forms\receive\exception\NoRollBackException;
use app\plugins\fission\forms\receive\validate\FacadeAdmin;
use yii\queue\JobInterface;

class ExchangeJob extends BaseJob implements JobInterface
{
    public $activity_log_id;
    public $reward_id;
    public $user;

    public function execute($queue)
    {
        $this->setRequest();
        $t = \Yii::$app->db->beginTransaction();
        try {
            /** @var User $user */
            $user = $this->user;
            $f = new FacadeAdmin();
            $f->unite($user, $this->activity_log_id, $this->reward_id);
            $activityLogModel = $f->validate->activityLogModel;

            //商城
            \Yii::$app->setMall(Mall::findOne(['id' => $user->mall_id]));

            //生成奖品
            /** FissionLog $log */
            $create = new Create();
            $log = $create->start(
                $user,
                $activityLogModel,
                $this->reward_id,
                $secondary
            );
            //发放奖品
            $reward = new Reward();
            $reward->reward(
                $this->user,
                $log,
                $secondary
            );
            $t->commit();
        } catch (NoRollBackException $e) {
            $t->commit();
            \Yii::error('红包墙现金和商品领取-兑换分开进行');
        } catch (ConvertException $e) {
            $t->rollBack();
            \Yii::error('红包墙奖品发放异常错误日志：' . $e->getMessage());
        } catch (\Exception $e) {
            $t->rollBack();
            \Yii::error('红包墙活动信息异常：' . $e->getMessage());
        }
    }
}
