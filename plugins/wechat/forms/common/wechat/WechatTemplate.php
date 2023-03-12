<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/31
 * Time: 4:58 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\common\wechat;

use app\helpers\CurlHelper;
use app\plugins\wechat\forms\Model;

class WechatTemplate extends Model
{
    public $accessToken;

    public function getResult($result)
    {
        if ($result['errcode'] == 0) {
            return $result;
        } else {
            throw new \Exception($result['errmsg']);
        }
    }

    /**
     * @param $template_id_short
     * @return mixed
     * @throws \Exception
     * 添加模板至账号下的模板库
     * https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html
     */
    public function addTemplate($template_id_short)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$this->accessToken}";
        $curl = CurlHelper::getInstance();
        $curl->setPostType(CurlHelper::BODY);
        $res = $curl->httpPost($api, [], [
            'template_id_short' => $template_id_short
        ]);
        return $this->getResult($res);
    }

    /**
     * @return mixed
     * @throws \Exception
     * 获取账号下的所有模板消息
     */
    public function getTemplateList()
    {
        $api = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token={$this->accessToken}";
        return CurlHelper::getInstance()->httpGet($api);
    }

    /**
     * @param array $arg
     * @return mixed
     * @throws \Exception
     * 发送模板消息
     */
    public function send($arg = [])
    {
        if (!isset($arg['touser']) || !$arg['touser']) {
            throw new \Exception('touser字段缺失，请填写接收者（用户）的 openid');
        }
        if (!isset($arg['template_id']) || !$arg['template_id']) {
            throw new \Exception('template_id字段缺失，请填写所需下发的订阅消息的id');
        }
        $api = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$this->accessToken}";
        $curl = CurlHelper::getInstance();
        $curl->setPostType(CurlHelper::BODY);
        $res = $curl->httpPost($api, [], $arg);
        \Yii::error($res['errmsg']);
        \Yii::error($arg);
        return $this->getResult($res);
    }
}
