<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\core;

use app\models\User;
use app\plugins\fission\forms\receive\basic\BaseAbstract;
use app\plugins\fission\forms\receive\basic\Cash;
use app\plugins\fission\forms\receive\basic\Goods;
use app\plugins\fission\forms\receive\exception\ConvertException;
use app\plugins\fission\forms\receive\exception\NoRollBackException;
use app\plugins\fission\models\FissionRewardLog;

class Reward
{
    private function method($type): string
    {
        $className = array_map(function ($item) {
            return ucfirst($item);
        }, explode("_", $type));

        $class = 'app\\plugins\\fission\\forms\\receive\\basic\\' . implode($className);
        if (!class_exists($class)) {
            die('CLASS EXISTS问题');
        }
        return $class;
    }

    public function re($user, $log, $reward, $secondary = null)
    {
        $method = $this->method($reward['status']);
        /** @var BaseAbstract $class */
        $class = new $method($user, $reward, $log);

        if ($class->exchange($message, $log)) {
            //阻断现金 + 商品
            if ($class instanceof Cash || $class instanceof Goods) {
                throw new NoRollBackException($message);
            }
            $log->is_exchange = 1;
            $log->save();
        } else {
            if ($reward['level'] === 'main'
                && in_array($reward['status'], ['goods', 'coupon', 'card'])
                && !empty($secondary)
            ) {
                $log->reward_id = $secondary['id'];
                $log->reward_type = $secondary['type'];
                $log->reward = \yii\helpers\BaseJson::encode($secondary);
                $log->real_reward = Create::getRealReward($secondary);
                $log->save();
                $this->re($user, $log, $secondary);
            } else {
                throw new ConvertException($message);
            }
        }
    }

    //自动发放
    public function reward(User $user, FissionRewardLog $log, $secondary = null)
    {
        /** FissionLog $log */
        $reward = \yii\helpers\BaseJson::decode($log->reward);
        $this->re($user, $log, $reward, $secondary);
    }
}
