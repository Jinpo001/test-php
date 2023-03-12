<?php
/**
 * @copyright ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2020/10/30
 * Time: 16:26
 */

namespace app\plugins\wechat\controllers\mall;

use app\forms\common\wechat\WechatFactory;
use app\forms\open3rd\ExtAppForm;
use app\forms\open3rd\Open3rd;
use app\models\Model;
use app\models\WxappPlatform;
use app\plugins\Controller;
use app\models\WechatWxmpprograms;
use yii\web\BadRequestHttpException;

class ThirdPlatformController extends Controller
{
    public function actionAuthorizer()
    {
        try {
            $platform = WxappPlatform::getPlatform();
            if (!$platform || empty($platform->component_access_token)) {
                throw new \Exception('未配置微信开放平台或者未收到推送ticket,请等待10分钟后再试');
            }
            $open3rd = new Open3rd([
                'appId' => $platform->appid,
                'appSecret' => $platform->appsecret,
                'componentAccessToken' => $platform->component_access_token,
                'auth_type' => 1
            ]);
            $res = $open3rd->getAuthorizerInfo();
            \Yii::error($res);
            $t = \Yii::$app->db->beginTransaction();
            $third = WechatFactory::getThird($res['authorization_info']['authorizer_appid']);
            if (!$third) {
                $third = new WechatWxmpprograms();
            } elseif ($third->mall_id != \Yii::$app->mall->id) {
                throw new \Exception('该账号已经绑定过其他商城');
            }
            $third->mall_id = \Yii::$app->mall->id;
            $third->authorizer_appid = $res['authorization_info']['authorizer_appid'];
            $third->authorizer_access_token = $res['authorization_info']['authorizer_access_token'];
            $third->authorizer_refresh_token = $res['authorization_info']['authorizer_refresh_token'];
            $third->authorizer_expires = time() + 7000;
            $third->func_info = json_encode($res['authorization_info']['func_info']);
            $third->is_delete = 0;
            $third->version = 2;
            if (!$third->save()) {
                throw new \Exception((new Model())->getErrorMsg($third));
            }
            $ext = ExtAppForm::instance($third, 0, 'wechat');
            $info = $ext->getAuthorizerInfo();
            if (!$info) {
                throw new \Exception('获取授权方的帐号基本信息');
            }
            if ($info) {
                $third->nick_name = $info['authorizer_info']['nick_name'];
                $third->head_img = $info['authorizer_info']['head_img'];
                $third->verify_type_info = $info['authorizer_info']['verify_type_info']['id'];
                $third->user_name = $info['authorizer_info']['user_name'];
                $third->qrcode_url = $info['authorizer_info']['qrcode_url'];
                $third->principal_name = $info['authorizer_info']['principal_name'];
                $third->principal_name = $info['authorizer_info']['principal_name'];
                $third->signature = $info['authorizer_info']['signature'];
            }
            if (!$third->save()) {
                throw new \Exception((new Model())->getErrorMsg($third));
            }
            $t->commit();
            return \Yii::$app->response->redirect(\Yii::$app->urlManager->createUrl(['plugin/wechat/mall/config/setting']));
        } catch (\Exception $exception) {
            if (isset($t)) {
                $t->rollBack();
            }
            \Yii::error($exception);
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
