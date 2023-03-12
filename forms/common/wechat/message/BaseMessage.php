<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/26
 * Time: 5:23 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\message;


use app\forms\common\wechat\WechatFactory;
use app\jobs\WechatCustomJob;
use app\models\Model;
use app\models\WechatSubscribeReply;

abstract class BaseMessage extends Model
{
    public $name;
    public $url;
    public $type = '';

    /**
     * @@param WechatSubscribeReply $model
     * @return mixed
     * 事件推送信息
     */
    abstract public function reply($model);

    /**
     * @return mixed
     * 素材id校验
     */
    abstract public function checkMedia();

    /**
     * @@param WechatSubscribeReply $model
     * @return mixed
     * 客服消息发送
     */
    abstract public function custom($model);

    /**
     * @param WechatSubscribeReply $model
     * @return string
     */
    public function getMedia($model)
    {
        if ($model->url === $this->url && $model->media_id) {
            return $model->media_id;
        }
        $fileName = $this->checkMedia();
        if (!$fileName) {
            return '0';
        }
        $file = file_uri('/web/temp/');
        $localPath = $file['local_uri'] . $fileName[0];
        // 将网络图片下载到本地
        file_put_contents($localPath, file_get_contents($this->url));
        $app = WechatFactory::create();
        $res = $app->mediaService->upload([
            'type' => $this->type,
            'media' => $localPath
        ]);
        // 使用完之后删除掉
        unlink($localPath);
        // 2天之后重新生成media_id
        \Yii::$app->queue3->delay(86400 * 2)->push(new WechatCustomJob([
            'mall' => \Yii::$app->mall,
            'media_id' => $res['media_id']
        ]));
        return $res['media_id'];
    }
}
