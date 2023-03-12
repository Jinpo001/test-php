<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2020/7/18
 * Time: 17:56
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\diy\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\plugins\diy\models\CoreTemplateType;
use Yii;

class SyncTemplateTypeForm extends Model
{
    public function sync()
    {
        // $time = 3600;
        // $cacheKey = 'TEMPLATE_TYPE_SYNC_TIME';
        // $lastSyncTime = Yii::$app->cache->get($cacheKey);
        // if ($lastSyncTime && (time() - $lastSyncTime) < $time) {
        //     return [
        //         'code' => ApiCode::CODE_SUCCESS,
        //         'msg' => 'synced recently, do nothing.',
        //     ];
        // }
        // $res = Yii::$app->cloud->template->getList([
        //     'dont_validate_domain' => 1,
        //     'page_size' => 100
        // ]);
        // $typeList = CoreTemplateType::find()->all();
        // $newTypeList = [];
        // foreach ($typeList as $type) {
        //     $newTypeList[$type['template_id']][$type['type']] = $type;
        // }
        // foreach ($res['list'] as $item) {
        //     $insert = $item['range'];
        //     if (isset($newTypeList[$item['id']])) {
        //         $local = array_keys($newTypeList[$item['id']]);
        //         $same = array_intersect($local, $item['range']); // 获取本地存储的和远端的交集
        //         $insert = array_diff($item['range'], $same);
        //         $delete = array_diff($local, $same);
        //         foreach ($delete as $range) {
        //             $model = $newTypeList[$item['id']][$range];
        //             $model->delete();
        //         }
        //     }
        //     foreach ($insert as $range) {
        //         $model = new CoreTemplateType();
        //         $model->template_id = $item['id'];
        //         $model->type = $range;
        //         $model->is_delete = 0;
        //         $model->save();
        //     }
        // }
        // Yii::$app->cache->set($cacheKey, time(), $time);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => 'sync ok.',
        ];
    }
}
