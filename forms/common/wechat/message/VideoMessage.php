<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/26
 * Time: 5:45 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\message;

class VideoMessage extends BaseMessage
{
    public $name = '回复视频消息';
    public $type = 'video';
    public $url;

    public function rules()
    {
        return [
            [['url'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'url' => '视频地址链接',
        ];
    }

    public function reply($model)
    {
        return [
            'Video' => [
                'MediaId' => $model->media_id,
            ]
        ];
    }

    public function checkMedia()
    {
        preg_match('/\w*\.mp4/', $this->url, $fileName);
        if (empty($fileName)) {
            throw new \Exception('上传视频的格式不正确，仅支持mp4格式的视频');
        }
        return $fileName;
    }

    public function custom($model)
    {
        return [
            'msgtype' => $this->type,
            'video' => [
                'media_id' => $model->media_id,
            ]
        ];
    }
}
