<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/4
 * Time: 11:01 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms\common;

use app\forms\common\qrcode\BdQrcode;
use app\plugins\wechat\forms\common\wechat\WechatConfig;
use app\plugins\wechat\Plugin;

class QrcodeServe extends BdQrcode
{
    public function getQrcode($args = [])
    {
        $plugin = new Plugin();
        $token = \Yii::$app->security->generateRandomString(30);
        // 获取二维码上跳转链接路径
        $base = rtrim($plugin->getWebUri(), '/');
        $params = [
            'scene' => $token
        ];
        if (isset($args['scene']['user_id'])) {
            $params['user_id'] = $args['scene']['user_id'];
        }
        $text = $base . $this->buildParams('/pages/index/index', $params);

        $imgName = md5(strtotime('now')) . '.jpg';
        // 获取图片存储的路径
        $res = file_uri('/web/temp/');
        $localUri = $res['local_uri'];
        $webUri = $res['web_uri'];
        $save_path = $localUri . $imgName;
        $args['width'] = $args['width'] ?? 430;
        $args['page'] = $args['page'] ?? 'pages/index/index';
        $args['scene'] = $args['scene'] ?? '';
        $size = floor($args['width'] / 37 * 100) / 100 + 0.01;
        \QRcode::png($text, $save_path, QR_ECLEVEL_L, $size, 2);
        $this->saveQrCodeParameter($token, $args['scene'], $args['page']);
        return $webUri . $imgName;
    }

    public function getWechatQrcode()
    {
        $plugin = new Plugin();
        $appid = $plugin->getWechat()->appId;
        $res = file_uri('/web/temp/');
        $imgName = md5($appid) . '.jpg';
        $img = $res['local_uri'] . $imgName;
        if (!file_exists($img)) {
            $wechatConfig = new WechatConfig();
            $args = [
                'access_token' => $plugin->getAccessToken(),
                'action_name' => 'QR_LIMIT_SCENE',
                'action_info' => [
                    'scene' => [
                        'scene_id' => 123
                    ]
                ]
            ];
            $qrcode = $wechatConfig->getQrcode($args);
            file_put_contents($img, file_get_contents($qrcode));
        }
        return $res['web_uri'] . $imgName;
    }
}
