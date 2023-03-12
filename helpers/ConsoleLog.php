<?php

namespace app\helpers;

class ConsoleLog extends \yii\log\FileTarget
{
    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        parent::init();
        $this->logFile = \Yii::$app->getRuntimePath() . '/logs/console.log';
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }
}
