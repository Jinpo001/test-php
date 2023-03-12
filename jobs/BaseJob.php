<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/24
 * Time: 10:14 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\jobs;

use yii\base\BaseObject;

class BaseJob extends BaseObject
{
    public $hostInfo;
    public $baseUrl;

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (\Yii::$app instanceof \yii\web\Application) {
            if (!$this->hostInfo) {
                $this->hostInfo = \Yii::$app->request->hostInfo;
            }
            if (!$this->baseUrl) {
                $this->baseUrl = \Yii::$app->request->baseUrl;
            }
        } else {
            if (!$this->hostInfo) {
                $this->hostInfo = \Yii::$app->hostInfo;
            }
            if (!$this->baseUrl) {
                $this->baseUrl = \Yii::$app->baseUrl;
            }
        }
        $this->setRequest();
    }

    public function setRequest()
    {
        \Yii::$app->setHostInfo($this->hostInfo);
        \Yii::$app->setBaseUrl($this->baseUrl);
    }
}
