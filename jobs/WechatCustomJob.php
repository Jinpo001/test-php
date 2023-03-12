<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/2
 * Time: 1:48 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;

use app\forms\common\wechat\WechatFactory;
use app\models\Mall;
use app\models\WechatSubscribeReply;
use yii\queue\JobInterface;

class WechatCustomJob extends BaseJob implements JobInterface
{
    /**
     * @var Mall $mall
     */
    public $mall;

    public $media_id;

    public function execute($queue)
    {
        $this->setRequest();
        \Yii::$app->setMall($this->mall);
        $query = WechatSubscribeReply::find()->where([
            'mall_id' => $this->mall->id,
            'media_id' => $this->media_id,
            'is_delete' => 0
        ]);
        \Yii::warning('临时素材重新生成' . $this->media_id);
        foreach ($query->each() as $model) {
            /* @var WechatSubscribeReply $model */
            $message = WechatFactory::createMessage($model->type);
            $message->url = $model->url;
            $model->media_id = '';
            $model->media_id = $message->getMedia($model);
            if (!$model->save()) {
                \Yii::warning($message->getErrorMsg($model));
            }
        }
    }
}
