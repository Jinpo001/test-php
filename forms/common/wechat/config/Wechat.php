<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/24
 * Time: 10:49 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\config;


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