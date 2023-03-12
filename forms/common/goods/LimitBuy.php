<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/12/17
 * Time: 3:15 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\goods;

use app\models\Goods;
use app\models\Mall;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;

class LimitBuy extends Model
{
    /**
     * @var Mall $mall
     */
    public $mall;

    /**
     * @var Goods $goods
     */
    public $goods;

    public $msg;

    public $user_id;

    public static function create($sign, $config = [])
    {
        if ($sign === '') {
            $sign = 'mall';
        }
        try {
            $plugin = \Yii::$app->plugin->getPlugin($sign);
            $limitBuy = $plugin->limitBuy($config);
        } catch (\Exception $exception) {
            \Yii::warning($exception);
            $limitBuy = new LimitBuy($config);
        }

        $limitBuy->user_id = $limitBuy->user_id ?: \Yii::$app->user->id;
        return $limitBuy;
    }

    /**
     * @return array
     * 获取限购情况
     */
    public function getLimitBuy()
    {
        $res = [
            'status' => $this->goods->limit_buy_status,
            'rest_number' => 0,
            'type' => 'day',
            'text' => '',
            'msg' => '',
            'value' => 0,
        ];
        if (
            ($this->goods->confine_count <= 0 && $this->goods->limit_buy_value <= 0)
            || $this->goods->limit_buy_status == 0
        ) {
            $res['status'] = 0;
            return $res;
        }
        $rest = null;
        // 组合限购  目前仅永久限购及每天、每周、每月组合
        $list = [
            'all' => $this->goods->confine_count,
            $this->goods->limit_buy_type => $this->goods->limit_buy_value
        ];
        // 查询用户总共购买商品数
        foreach ($list as $key => $value) {
            if ($value > 0) {
                $num = $this->getBuyNumber($key);
                $tempRest = $value - $num;
                if ($rest === null || $tempRest < $rest) {
                    $rest = $tempRest;
                    $res['type'] = $key;
                    $res['msg'] = $this->getText($value);
                    $res['text'] = $this->getLimitBuyText($value, $num);
                    $res['rest_number'] = $rest;
                    $res['value'] = $value;
                }
                if ($rest <= 0) {
                    $res['rest_number'] = 0;
                    return $res;
                }
            }
        }
        return $res;
    }

    /**
     * @param $limitBuyType
     * @return bool|int|mixed|string|null
     * 获取已购买金额
     */
    protected function getBuyNumber($limitBuyType)
    {
        switch ($limitBuyType) {
            case 'day':
                $time = mysql_timestamp(strtotime('today'));
                $this->msg = '天';
                break;
            case 'week':
                $week = date('w');
                $time = date('Y-m-d 00:00:00', strtotime('-' . (($week == 0 ? 7 : $week) - 1) . 'day'));
                $this->msg = '周';
                break;
            case 'month':
                $time = date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));
                $this->msg = '月';
                break;
            default:
                $time = 0;
                $this->msg = '';
        }
        return $this->getOrderNum($time);
    }

    /**
     * @param $time
     * @return bool|int|mixed|string|null
     */
    protected function getOrderNum($time)
    {
        return OrderDetail::find()->alias('od')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where([
                'od.goods_id' => $this->goods->id,
                'od.is_delete' => 0,
                'o.user_id' => $this->user_id,
                'o.is_delete' => 0,
            ])
            ->keyword($time, ['between', 'o.created_at', $time, mysql_timestamp()])
            ->andWhere(['!=', 'o.cancel_status', 1])
            ->sum('od.num');
    }

    /**
     * @param $number
     * @return string
     * 附加上单位后缀
     */
    protected function appendUnit($number)
    {
        return intval($number) . $this->goods->unit;
    }

    /**
     * @param $value
     * @param $num
     * @return string
     * 获取限购提示文字 例如：该商品每天限购10件，您今天已购买9件
     */
    protected function getLimitBuyText($value, $num)
    {
        $text = '该商品%s,您%s已购买%s';
        $pre = $this->msg == '天' ? '今' : '本';
        return sprintf($text, $this->getText($value), $this->appendMsg($this->msg, $pre), $this->appendUnit($num));
    }

    /**
     * @param $value
     * @return string
     * 获取限购提示文字 例如：每天限购10件
     */
    protected function getText($value)
    {
        return sprintf('%s限购%s', $this->appendMsg($this->msg, '每'), $this->appendUnit($value));
    }

    /**
     * @param $msg
     * @param $pre
     * @return string
     * 附加上自定义前缀，如果值为空则不附加
     */
    protected function appendMsg($msg, $pre)
    {
        if (!$msg) {
            return $msg;
        }
        return $pre . $msg;
    }

    /**
     * @var $errorMsg
     * 错误提示
     */
    public $errorMsg;

    public function checkMinNumber($num)
    {
        if ($this->goods->min_number > $num) {
            $this->errorMsg = '商品（' . $this->goods->goodsWarehouse->name . '）'
                . $this->appendUnit($this->goods->min_number) . '起售';
            return false;
        }
        return true;
    }

    public function checkLimitBuy($num)
    {
        $res = $this->getLimitBuy();
        if ($res['status'] === 1 && $res['rest_number'] < $num) {
            $this->errorMsg = '商品（' . $this->goods->goodsWarehouse->name . '）' . $res['msg'];
            return false;
        }
        return true;
    }
}
