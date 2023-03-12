<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/10/27
 * Time: 2:54 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\template\order_pay_template;

use app\forms\common\template\tplmsg\BaseTemplate;
use app\models\Model;

/**
 * Class BaseInfo
 * @package app\forms\common\template\tplmsg
 * @property BaseTemplate $sendClass
 * @property string $key
 * @property string $chineseName
 */
abstract class BaseInfo extends Model
{
    protected $key;
    protected $chineseName;
    protected $sendClass;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->sendClass = $this->getSendClass();
        $config = $this->configAll();
        $dataKey = [];
        foreach ($config as $key => $value) {
            if (isset($value['config']['data'])) {
                $dataKey[$key] = $value['config']['data'];
            }
        }
        $this->sendClass->dataKey = $dataKey;
    }

    abstract public function getSendClass();

    public function getKey()
    {
        return $this->key;
    }

    public function getChineseName()
    {
        return $this->chineseName;
    }

    public function send($args)
    {
        foreach ($args as $key => $value) {
            if (property_exists($this->sendClass, $key)) {
                $this->sendClass->$key = $value;
            }
        }
        return $this->sendClass->send();
    }

    public function test($args)
    {
        foreach ($args as $key => $value) {
            if (property_exists($this->sendClass, $key)) {
                $this->sendClass->$key = $value;
            }
        }
        return $this->sendClass->test();
    }

    abstract public function configAll();

    public function config($platform)
    {
        $config = $this->configAll();
        return $config[$platform] ?? [];
    }
}
