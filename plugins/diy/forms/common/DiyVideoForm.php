<?php


namespace app\plugins\diy\forms\common;


use app\models\Model;
use app\models\Video;

class DiyVideoForm extends Model
{
    static private $list = [];

    /**
     * @param $array
     */
    public function getVideoList($array)
    {
        self::$list = Video::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0,
            'id' => $array
        ])->asArray()->all();
    }

    public function getNewData($data)
    {
        if ($data['addType'] === 'auto') {
            $sentinel = true;
            for ($i = 0; $i < count(self::$list); $i++) {
                $item = self::$list[$i];
                if ($data['video_id'] === $item['id']) {
                    $sentinel = false;
                    $data['pic_url'] = $item['pic_url'];
                    $data['url'] = $item['url'];
                    break;
                }
            }
            if ($sentinel) return [];
        }
        $data['url'] = \app\forms\common\video\Video::getUrl($data['url']);
        return $data;
    }
}