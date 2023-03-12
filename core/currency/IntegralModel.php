<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/30
 * Time: 17:23
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\core\currency;


use app\forms\common\message\MessageService;
use app\forms\common\platform\PlatformConfig;
use app\models\IntegralLog;
use app\models\Mall;
use app\models\Model;
use app\models\User;
use yii\db\Exception;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class IntegralModel extends Model implements BaseCurrency
{
    public $mall;
    public $user;
    public $type;// 积分类型：1=收入，2=支出

    /**
     * @param $integral
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws Exception
     */
    public function add($integral, $desc, $customDesc = '', $orderNo = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_int($integral)) {
            throw new Exception('积分必须是整数类型');
        }
        $t = \Yii::$app->db->beginTransaction();
        $this->user->userInfo->integral += $integral;
        $this->user->userInfo->total_integral += $integral;
        if ($this->user->userInfo->save()) {
            try {
                $this->createLog(1, $integral, $desc, $customDesc, $orderNo);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->getErrorMsg($this->user->userInfo), $this->user->userInfo->errors, 1);
        }
    }

    /**
     * @param $integral
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws Exception
     */
    public function sub($integral, $desc, $customDesc = '', $orderNo = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_int($integral)) {
            throw new Exception('积分必须是整数类型');
        }
        if ($this->user->userInfo->integral < $integral) {
            throw new Exception('用户积分不足');
        }
        $t = \Yii::$app->db->beginTransaction();
        $this->user->userInfo->integral -= $integral;
        if ($this->user->userInfo->save()) {
            try {
                $this->createLog(2, $integral, $desc, $customDesc, $orderNo);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->getErrorMsg($this->user->userInfo), $this->user->userInfo->errors, 1);
        }
    }

    /**
     * @return integer
     */
    public function select()
    {
        return intval($this->user->userInfo->integral);
    }

    /**
     * @return integer
     */
    public function selectTotal()
    {
        return intval($this->user->userInfo->total_integral);
    }

    /**
     * @param $integral
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws Exception
     */
    public function refund($integral, $desc, $customDesc = '', $orderNo = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_int($integral)) {
            throw new Exception('积分必须是整数类型');
        }
        $t = \Yii::$app->db->beginTransaction();
        $this->user->userInfo->integral += $integral;
        if ($this->user->userInfo->save()) {
            try {
                $this->createLog(1, $integral, $desc, $customDesc, $orderNo);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->getErrorMsg($this->user->userInfo), $this->user->userInfo->errors, 1);
        }
    }

    /**
     * @param $type
     * @param $integral
     * @param $desc
     * @param string $customDesc
     * @param string $orderNo
     * @return bool
     * @throws \Exception
     */
    private function createLog($type, $integral, $desc, $customDesc = '', $orderNo = '')
    {
        if ($integral == 0) {
            \Yii::warning('积分为' . $integral . '不记录日志');
            return true;
        }
        if (!$customDesc) {
            $customDesc = \Yii::$app->serializer->encode(['msg' => '用户积分变动说明']);
        }
        $form = new IntegralLog();
        $form->user_id = $this->user->id;
        $form->mall_id = $this->user->mall_id;
        $form->type = $type;
        $form->integral = $integral;
        $form->desc = $desc;
        $form->custom_desc = $customDesc;
        $form->order_no = $orderNo;
        if ($form->save()) {
            $this->sendSmsToUser($type, $integral);
            return true;
        } else {
            throw new \Exception($this->getErrorMsg($form), $form->errors, 1);
        }
    }

    public function getLogListByUser()
    {
        $list = IntegralLog::find()->where([
            'mall_id' => $this->mall->id,
            'user_id' => $this->user->id,
            'type' => $this->type,
        ])
            ->page($pagination)
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }

    public function sendSmsToUser($type, $integral)
    {
        try {
            \Yii::warning('----积分变动消息发送提醒----');
            if (!$this->user->mobile) {
                throw new \Exception('用户未绑定手机号无法发送');
            }
            $messageService = new MessageService();
            $messageService->user = $this->user;
            $messageService->content = [
                'mch_id' => 0,
                'args' => [$type == 1 ? '增加' : '减少', $integral, $this->user->userInfo->integral]
            ];
            $messageService->platform = PlatformConfig::getInstance()->getPlatform($this->user);
            $messageService->tplKey = 'integral';
            $res = $messageService->templateSend();
        } catch (\Exception $exception) {
            \Yii::error('向用户发送短信消息失败');
            \Yii::error($exception);
        }
    }
}
