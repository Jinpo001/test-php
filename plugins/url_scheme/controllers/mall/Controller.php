<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/26
 * Time: 13:47
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\url_scheme\controllers\mall;

use app\plugins\url_scheme\Plugin;

class Controller extends \app\plugins\Controller
{
    public $sign;

    public function init()
    {
        parent::init();
        $this->sign = (new Plugin())->getName();
    }
}
