<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: jack_guo
 */

namespace app\forms\mall\export;

use app\forms\mall\export\jobs\ExportJob;
use app\models\Model;

class CommonExport extends Model
{
    public static function handle(array $params)
    {
        if (!isset($params['export_class']) || !is_string($params['export_class'])) {
            throw new \Exception('参数 export_class 必传且为字符串');
        }

        if (!isset($params['params']) || !is_array($params['params'])) {
            throw new \Exception('参数 params 必传且为数组');
        }
        
        $dataArr = [
            'export_class' => $params['export_class'],
            'mall' => \Yii::$app->mall,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'user' => \Yii::$app->user->identity,
            'params' => $params['params'],
        ];

        if (isset($params['model_class']) && isset($params['function_name'])) {
            $dataArr['model_class'] = $params['model_class'];
            $dataArr['function_name'] = $params['function_name'];
            $dataArr['get_data'] = \Yii::$app->request->get();
            $dataArr['post_data'] = \Yii::$app->request->post();

            if (isset($params['model_params']) && is_array($params['model_params'])) {
                $dataArr['model_params'] = $params['model_params'];
            }

            if (isset($params['function_params']) && is_array($params['function_params'])) {
                $dataArr['function_params'] = $params['function_params'];
            }
        }

        $second = 600;
        if (isset($params['second']) && $params['second'] > 600) {
            $second = $params['second'];
        }

        $class = new ExportJob($dataArr);
        $queueId = \Yii::$app->queue4->delay(0)->ttr($second)->push($class);

        return $queueId;
    }

    public static function getSecond($count)
    {
        if ($count <= 0) {
            throw new \Exception('count 必须大于0');
        }

        $second = ceil($count / 5);

        return $second;
    }
}
