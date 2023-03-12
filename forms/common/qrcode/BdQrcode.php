<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/4
 * Time: 10:00 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\qrcode;


use app\models\Model;
use app\models\QrCodeParameter;
use Grafika\Color;
use Grafika\Grafika;
use Grafika\ImageInterface;
use GuzzleHttp\Client;

abstract class BdQrcode extends Model
{
    abstract public function getQrcode($args = []);

    protected function buildParams($url, $array)
    {
        $query = http_build_query($array);
        $url = trim($url, '?');
        $url = trim($url, '&');
        if (mb_stripos($url, '?')) {
            return rtrim($url . '&' . $query, '&');
        } else {
            return rtrim($url . '?' . $query, '?');
        }
    }

    protected function saveQrCodeParameter($token, $data, $page)
    {
        $model = new QrCodeParameter();
        $model->user_id = \Yii::$app->user->id ?: 0;
        $model->mall_id = \Yii::$app->mall->id;
        $model->token = $token;
        $model->path = $page;
        $model->data = \Yii::$app->serializer->encode($data);
        $res = $model->save();

        if (!$res) {
            throw new \Exception($this->getErrorMsg($model));
        }

        return $model;
    }

    protected function getClient()
    {
        return new Client([
            'verify' => false,
            'Content-Type' => 'image/jpeg'
        ]);
    }

    protected function post($url, $body = array())
    {
        $response = $this->getClient()->post($url, [
            'body' => json_encode($body)
        ]);

        return $response;
    }

    protected function get($url, $params = array())
    {
        $response = $this->getClient()->get($this->buildParams($url, $params));
        return json_decode($response->getBody(), true);
    }

    //保存图片内容到临时文件
    protected function saveTempImageByContent($content)
    {
        $imgName = md5(base64_encode($content)) . '.jpg';
        $res = file_uri('/web/temp');
        $localPath = $res['local_uri'] . '/' . $imgName;
        $fp = fopen($localPath, 'w');
        fwrite($fp, $content);
        fclose($fp);
        return $res['web_uri'] . '/' . $imgName;
    }
}
