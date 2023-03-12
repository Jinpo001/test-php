<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/5/23
 * Time: 11:37:00
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\core\cloud;


class CloudCollect extends CloudBase
{
    public $classVersion = '4.2.10';

    public function collect($id)
    {
        $api = "/mall/copy/index";
        return $this->httpGet($api, ['vid' => $id]);
    }
}
