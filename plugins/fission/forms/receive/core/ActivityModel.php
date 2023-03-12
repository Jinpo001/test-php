<?php


namespace app\plugins\fission\forms\receive\core;


use app\plugins\fission\models\FissionActivity;

class ActivityModel
{
    protected $model;

    public function __construct($model)
    {
        if ($model instanceof FissionActivity) {
            $this->model = \yii\helpers\ArrayHelper::toArray($model);
        } else {
            throw new \Exception('ERROR 1');
        }
    }

    public function __toString()
    {
        extract($this->model);
        $arr = [
            'id' => $id,
            'name' => $name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'number' => $number,
            'expire_time' => $expire_time
        ];
        return \yii\helpers\BaseJson::encode($arr);
    }
}