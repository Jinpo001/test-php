<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/1
 * Time: 11:03 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\service;

use app\helpers\CurlHelper;

class CustomService extends BaseService
{
    /**
     * @param array $args ['touser' => 'OPENID', 'msgtype' => '']
     * @return mixed
     * @throws \Exception
     * 客服消息发送
     * https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Service_Center_messages.html
     */
    public function send($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$this->accessToken}";
        $res = $this->getClient()->setPostType(CurlHelper::BODY)->httpPost($api, [], $args);
        return $this->getResult($res);
    }
}
