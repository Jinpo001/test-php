<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/6
 * Time: 1:51 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;

use app\helpers\CurlHelper;

class DeliveryService extends BaseService
{
    public function getClient()
    {
        return CurlHelper::getInstance()->setPostType(CurlHelper::BODY);
    }

    /**
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/delivery/get_company_list.html
     * 获取快递公司列表
     */
    public function getCompanyList()
    {
        $api = "https://api.weixin.qq.com/shop/delivery/get_company_list?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/delivery/send.html
     * 订单发货
     */
    public function send($args)
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
        $params['finish_all_delivery'] = $args['finish_all_delivery'];
        $params['delivery_list'] = $args['delivery_list'];
        $api = "https://api.weixin.qq.com/shop/delivery/send?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/delivery/recieve.html
     * 订单确认收货
     */
    public function receive($args)
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
        $api = "https://api.weixin.qq.com/shop/delivery/recieve?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }
}
