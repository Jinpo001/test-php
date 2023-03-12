<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */


namespace app\plugins\community\forms\api\poster_share;


use app\forms\api\poster\common\BaseConst;
use app\forms\api\poster\common\CommonFunc;
use app\plugins\community\forms\Model;

class PosterCustomize extends Model implements BaseConst
{
    use CommonFunc;

    public function traitHash($class)
    {
        return array_merge(['id' => $class->activity_id, 'middleman_id' => $class->middleman_id], $class->poster_arr);
    }

    public function traitQrcode($class)
    {
        return [
            ['id' => $class->activity_id, 'user_id' => \Yii::$app->user->id, 'middleman_id' => $class->middleman_id],
            240,
            'plugins/community/activity/activity'
        ];
    }
}
