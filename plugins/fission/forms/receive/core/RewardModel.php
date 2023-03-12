<?php


namespace app\plugins\fission\forms\receive\core;


use app\plugins\fission\models\FissionActivity;

class RewardModel
{
    protected $model;

    public function __construct($model)
    {
        if ($model instanceof FissionActivity) {
            $this->model = \yii\helpers\ArrayHelper::toArray($model->rewards);
        } else {
            throw new \Exception('ERROR 2');
        }
    }

    public function __toString()
    {
        function r($value)
        {
            extract($value);
            return [
                'id' => $id,
                'activity_id' => $activity_id,
                'type' => $type,
                'status' => $status,
                'people_number' => $people_number,
                'model_id' => $model_id,
                'exchange_type' => $exchange_type,
                'min_number' => $min_number,
                'max_number' => $max_number,
                'send_type' => $send_type,
                'level' => $level,
                'attr_id' => $attr_id,
            ];
        }

        if (is_array($this->model)) {
            $arr = array_map(function ($item) {
                return r($item);
            }, $this->model);
        } else {
            $arr = r($this->model);
        }
        return \yii\helpers\BaseJson::encode($arr);
    }
}