<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2020/11/25
 * Time: 9:28 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wechat\forms;


class Wechat extends \luweiss\Wechat\Wechat
{
    public $name;
    public $logo;
    public $qrcode;

    public function getInfo()
    {
        return [
            'name' => $this->name,
            'logo' => $this->logo,
            'qrcode' => $this->qrcode,
        ];
    }
}
