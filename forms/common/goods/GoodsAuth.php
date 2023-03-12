<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/12/21
 * Time: 2:15 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\goods;

use app\jobs\GoodsDownJob;
use app\jobs\RemindJob;
use app\models\Goods;
use app\models\Model;

class GoodsAuth extends Model
{
    /**
     * @var bool $is_show_and_buy_auth
     * 是否支持会员等级浏览和购买权限
     */
    public $is_show_and_buy_auth = true;

    /**
     * @var bool $is_min_number
     * 是否支持起售
     */
    public $is_min_number = true;

    /**
     * @var bool $is_limit_buy
     * 是否支持限购
     */
    public $is_limit_buy = true;

    /**
     * @var bool $is_setting_send_type
     * 是否支持单商品设置发货方式
     */
    public $is_setting_send_type = true;

    /**
     * @var bool $is_time
     * 是否支持销售时间
     */
    public $is_time = true;
    public $user;

    public static function defaultAuth()
    {
        return [
            'is_show_and_buy_auth' => true,
            'is_min_number' => true,
            'is_limit_buy' => true,
            'is_setting_send_type' => true,
            'is_time' => true
        ];
    }

    public static function create($sign, $config = [])
    {
        if ($sign === '') {
            $sign = 'mall';
        }
        try {
            $plugin = \Yii::$app->plugin->getPlugin($sign);
            $localConfig = $plugin->goodsAuth();
        } catch (\Exception $exception) {
            $localConfig = static::defaultAuth();
        }

        $goodsAuth = new GoodsAuth(array_merge($localConfig, $config));
        $goodsAuth->user = $goodsAuth->user ?: \Yii::$app->user->identity;
        return $goodsAuth;
    }

    protected function getLevel()
    {
        $level = 0;
        if ($this->user) {
            $level = $this->user->identity->member_level;
        }
        return [-1, $level];
    }

    /**
     * @param Goods $goods
     * @param string $key show_goods_auth|buy_goods_auth
     * @return bool
     * @throws \Exception
     */
    public function checkShowBuyAuth($goods, $key)
    {
        if (!$this->is_show_and_buy_auth) {
            return true;
        }
        $levelArr = $this->getLevel();
        if ($goods->is_setting_show_and_buy_auth == 1) {
            $levelAuth = $goods->$key;
        } else {
            $globalAuth = \Yii::$app->mall->getMallSetting([$key]);
            $levelAuth = $globalAuth[$key];
        }
        $levelAuthArr = explode(',', $levelAuth);
        return !empty(array_intersect($levelArr, $levelAuthArr));
    }

    /**
     * @param Goods $goods
     * @return bool
     * @throws \Exception
     * 校验浏览权限
     */
    public function checkShowAuth($goods)
    {
        return $this->checkShowBuyAuth($goods, 'show_goods_auth');
    }

    /**
     * @param Goods $goods
     * @return bool
     * @throws \Exception
     * 校验购买权限
     */
    public function checkBuyAuth($goods)
    {
        return $this->checkShowBuyAuth($goods, 'buy_goods_auth');
    }

    /**
     * @param Goods $goods
     * @return bool
     * 校验是否在销售中
     */
    public function checkTime($goods)
    {
        if (!$this->is_time || $goods->is_time != 1) {
            return true;
        }
        $nowTime = time();
        return $nowTime >= strtotime($goods->sell_begin_time) && $nowTime < strtotime($goods->sell_end_time);
    }

    /**
     * @param Goods $goods
     * @param array $sendType 默认发货方式
     * @return array|false|string[]
     * 获取商品支持的发货方式
     */
    public function getSendType($goods, $sendType = [])
    {
        if (!$this->is_setting_send_type || $goods->is_setting_send_type == 0) {
            $goodsSendType = (array)$sendType;
        } else {
            $goodsSendType = $goods->send_type ? explode(',', $goods->send_type) : [];
        }
        if ($goods->sign == 'mch') {
            foreach ($goodsSendType as $key => $sendType) {
                if ($sendType == 'city') {
                    unset($goodsSendType[$key]);
                }
            }
        }
        return array_values($goodsSendType);
    }

    /**
     * @param Goods $goods
     * @return int|mixed
     * 获取开售倒计时
     */
    public function getSellTime($goods)
    {
        if (!$this->is_time || $goods->is_time != 1) {
            return 0;
        }
        return max(0, intval(strtotime($goods->sell_begin_time) - time()));
    }

    /**
     * @param Goods $goods
     * @return bool
     * 校验是否结束销售
     */
    public function checkFinishSell($goods)
    {
        if (!$this->is_time || $goods->is_time != 1) {
            return $goods->status == 0;
        }
        $nowTime = time();
        return $nowTime >= strtotime($goods->sell_end_time);
    }

    /**
     * @param Goods $goods
     * @param integer $user_id 通知的对象 0--通知所有用户
     * @return bool
     * 添加开售提醒队列
     * @throws \Exception
     */
    public function pushRemind($goods, $user_id = 0)
    {
        $time = strtotime($goods->sell_begin_time) - time() - 5 * 60;
        \Yii::$app->queue3->delay($time < 0 ? 0 : $time)->push(new RemindJob([
            'goods' => $goods,
            'mall' => \Yii::$app->mall,
            'user_id' => $user_id,
            'sell_start_time' => $goods->sell_begin_time
        ]));
        return true;
    }

    /**
     * @param Goods $goods
     * @return bool
     * 添加自动下架队列
     * @throws \Exception
     */
    public function pushDown($goods)
    {
        $time = strtotime($goods->sell_end_time) - time();
        \Yii::$app->queue3->delay($time < 0 ? 0 : $time)->push(new GoodsDownJob([
            'goods' => $goods,
            'mall' => \Yii::$app->mall,
            'sell_end_time' => $goods->sell_end_time
        ]));
        return true;
    }
}
