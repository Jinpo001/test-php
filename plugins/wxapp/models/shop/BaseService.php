<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/5
 * Time: 9:29 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;

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
            case 48001:
                throw new \Exception('接口没有权限');
            default:
                throw new \Exception($result['errmsg']);
        }
    }
}
