<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/2/26
 * Time: 11:50:00
 */

namespace app\core\cloud;


class CloudWxapp extends CloudBase
{
    public $classVersion = '4.2.10';

    public function login($args)
    {
        return $this->httpGet('/mall/app-upload/login', $args);
    }

    public function preview($args)
    {
        return $this->httpGet('/mall/app-upload/preview', $args);
    }

    public function upload($args)
    {
        return $this->httpGet('/mall/app-upload/upload', $args);
    }
}
