<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/6
 * Time: 3:59 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;

use app\helpers\CurlHelper;

class SaleService extends BaseService
{
    public function getClient()
    {
        return CurlHelper::getInstance()->setPostType(CurlHelper::BODY);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/aftersale/add.html
     * 创建售后
     */
    public function add($args)
    {
        $api = "https://api.weixin.qq.com/shop/aftersale/add?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'out_order_id' => $args['out_order_id'],
            'out_aftersale_id' => $args['out_aftersale_id'],
            'openid' => $args['openid'],
            'type' => $args['type'],
            'create_time' => $args['create_time'],
            'status' => $args['status'],
            'finish_all_aftersale' => $args['finish_all_aftersale'],
            'product_infos' => $args['product_infos']
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/aftersale/get.html
     * 获取订单下售后单
     */
    public function get($args)
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
        $api = "https://api.weixin.qq.com/shop/aftersale/get?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/aftersale/update.html
     * 只能更新售后状态
     */
    public function update($args)
    {
        $params = [];
        if (isset($args['order_id'])) {
            $params['order_id'] = $args['order_id'];
        } elseif (isset($args['out_order_id'])) {
            $params['out_order_id'] = $args['out_order_id'];
        } else {
            throw new \Exception('缺少订单id');
        }
        $params['out_aftersale_id'] = $args['out_aftersale_id'];
        $params['status'] = $args['status'];
        $params['finish_all_aftersale'] = $args['finish_all_aftersale'];
        $api = "https://api.weixin.qq.com/shop/aftersale/update?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }
}
