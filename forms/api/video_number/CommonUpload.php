<?php

namespace app\forms\api\video_number;

use Grafika\Grafika;
use app\forms\AttachmentUploadForm;
use app\models\Model;

class CommonUpload extends Model
{
    /**
     * [uploadImage description]
     * @param  string $accessToken [微信token]
     * @param  string $picUrl      [图片链接]
     * @param  int    $size        [图片大小限制 单位:MB]
     * @param  array  $pxSize      [图片宽高限制]
     * @param  array  $autoExactPx [自动裁剪]
     * @return string              [图片ID]
     */
    public function uploadImage($accessToken, $picUrl, $size = 10)
    {
        $picUrl = $this->handlePicUrl($picUrl);
        $filename = md5($picUrl) . '.jpg';
        $path = \Yii::$app->basePath . '/web/temp/video_number/' . date('Y') . date('m') . date('d') . '/';
        $localUrl = $path . $filename;
        try {
            $content = $this->getCurlContents($picUrl);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fp = fopen($localUrl, "a"); //将文件绑定到流
            fwrite($fp, $content); //写入文件

            $imageInfo = (new AttachmentUploadForm())->getInstanceFromFile($localUrl);
            if ($imageInfo->size >= $size * 1024 * 1024) {
                throw new \Exception("图片大小不能超过" . $size . 'MB');
            }


            $api = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$accessToken}&type=image";
            $res = CommonVideoNumber::postFile($api, [
                [
                    'name' => 'media',
                    'contents' => fopen($imageInfo->tempName, 'r'),
                ],
            ]);

            $res = json_decode($res->getBody()->getContents(), true);

            if (isset($res['media_id'])) {
                $this->removeFile($localUrl);
                return $res['media_id'];
            } else {
                throw new \Exception($res['errmsg']);
            }
        } catch (\Exception $exception) {
            $this->removeFile($localUrl);
            throw $exception;
        }
    }

    public function uploadImageReturnUrl($accessToken, $picUrl, $size = 1)
    {
        $picUrl = $this->handlePicUrl($picUrl);
        $filename = md5($picUrl) . '.jpg';
        $path = \Yii::$app->basePath . '/web/temp/video_number/' . date('Y') . date('m') . date('d') . '/';
        $localUrl = $path . $filename;
        try {
            $content = $this->getCurlContents($picUrl);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fp = fopen($localUrl, "a"); //将文件绑定到流
            fwrite($fp, $content); //写入文件

            $imageInfo = (new AttachmentUploadForm())->getInstanceFromFile($localUrl);
            if ($imageInfo->size >= $size * 1024 * 1024) {
                throw new \Exception("图片大小不能超过" . $size . 'MB');
            }


            $api = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token={$accessToken}&type=image";
            $res = CommonVideoNumber::postFile($api, [
                [
                    'name' => 'media',
                    'contents' => fopen($imageInfo->tempName, 'r'),
                ],
            ]);

            $res = json_decode($res->getBody()->getContents(), true);

            if (isset($res['url'])) {
                $this->removeFile($localUrl);
                return $res['url'];
            } else {
                throw new \Exception($res['errmsg']);
            }
        } catch (\Exception $exception) {
            $this->removeFile($localUrl);
            throw $exception;
        }
    }

    private function handlePicUrl($picUrl)
    {
        if(strpos($picUrl,'https') === false && strpos($picUrl,'http') === false){ 
            $picUrl = 'http:' . $picUrl;
        }

        return $picUrl;
    }

    private function removeFile($localUrl)
    {
        if (file_exists($localUrl)) {
            unlink($localUrl);
        }
    }

    private function getCurlContents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $returnContent = ob_get_contents();
        ob_end_clean();

        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($returnCode != 200) {
            $returnContent = file_get_contents($url);
            if (!$returnContent) {
                throw new \Exception('图片异常');
            }
        }

        return $returnContent;
    }
}
