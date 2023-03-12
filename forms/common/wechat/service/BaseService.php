<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/26
 * Time: 2:14 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\service;

use app\helpers\CurlHelper;
use app\models\Model;

class BaseService extends Model
{
    public $accessToken;

    public function getClient()
    {
        return CurlHelper::getInstance();
    }

    public function getResult($result)
    {
        if (!isset($result['errcode'])) {
            return $result;
        }
        \Yii::warning($result);
        switch ($result['errcode']) {
            case 0:
                return $result;
            case 45064:
                throw new \Exception('填写的小程序appid和公众号没有绑定');
                break;
            case 61007:
                throw new \Exception('三方平台没有授权接口');
                break;
            case 40164:
                throw new \Exception('服务器ip为添加到公众号IP白名单中');
                break;
            case 40006:
                throw new \Exception('上传的文件超过限制，图片、视频不得超过10M，语音不得超过2M');
                break;
            default:
                throw new \Exception($result['errmsg']);
        }
    }
}
