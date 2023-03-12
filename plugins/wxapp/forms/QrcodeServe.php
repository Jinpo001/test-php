<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/24
 * Time: 4:46 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\forms;

use app\forms\common\qrcode\BdQrcode;
use app\plugins\wxapp\Plugin;

class QrcodeServe extends BdQrcode
{
    /**
     * @var Plugin $plugin
     */
    public $plugin;

    public function init()
    {
        parent::init();
        $this->plugin = new Plugin();
    }

    public function getQrcode($args = [])
    {
        $accessToken = $this->plugin->getAccessToken();
        if (!$accessToken) {
            throw new \Exception('微信配置有误');
        }
        $width = $args['width'] ?? 430;
        $scene = $args['scene'] ?? '';
        $page = $args['page'] ?? null;

        $token = \Yii::$app->security->generateRandomString(30);
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$accessToken}";
        $res = $this->post($api, [
            'scene' => $token,
            'width' => $width,
        ]);

        if ($res->getStatusCode() == 200) {
            $parameter = $this->saveQrCodeParameter($token, $scene, $page);
            if (in_array('image/jpeg', $res->getHeader('Content-Type'))) {
                //返回图片
                $url = $this->saveTempImageByContent($res->getBody()->getContents());
                return $url;
            } else {
                //返回文字
                $res = json_decode($res->getBody()->getContents(), true);
                throw new \Exception($res['errmsg']);
            }
        } else {
            throw new \Exception($res['errmsg']);
        }
    }
}
