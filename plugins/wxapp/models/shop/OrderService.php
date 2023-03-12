<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/5
 * Time: 4:31 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;

use app\helpers\CurlHelper;

class OrderService extends BaseService
{
    public function getClient()
    {
        return CurlHelper::getInstance()->setPostType(CurlHelper::BODY);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/order/check_scene.html
     * 检查场景值是否在支付校验范围内
     */
    public function check($args)
    {
        $api = "https://api.weixin.qq.com/shop/scene/check?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'scene' => $args['scene']
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/order/add_order.html
     * 生成订单并获取ticket
     */
    public function add($args)
    {
        $api = "https://api.weixin.qq.com/shop/order/add?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $args);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/order/pay_order.html
     * 同步订单支付结果
     */
    public function pay($args)
    {
        $params = [];
        if (isset($args['order_id'])) {
            $params['order_id'] = $args['order_id'];
        } elseif (isset($args['out_order_id'])) {
            $params['out_order_id'] = $args['out_order_id'];
        } else {
            throw new \Exception('缺少订单id');
        }
        $params['openid'] = $args['openid'];
        $params['action_type'] = $args['action_type'];
        $params['action_remark'] = $args['action_remark'];
        if ($params['action_type'] == 1) {
            $params['transaction_id'] = $args['transaction_id'];
            $params['pay_time'] = $args['pay_time'];
        }
        $api = "https://api.weixin.qq.com/shop/order/pay?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/order/get_order.html
     * 获取订单详情
     */
    public function get($args)
    {
        $params['openid'] = $args['openid'];
        if (isset($args['order_id'])) {
            $params['order_id'] = $args['order_id'];
        } elseif (isset($args['out_order_id'])) {
            $params['out_order_id'] = $args['out_order_id'];
        } else {
            throw new \Exception('缺少订单id');
        }
        $api = "https://api.weixin.qq.com/shop/order/get?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }
}
