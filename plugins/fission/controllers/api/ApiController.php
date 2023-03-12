<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/21
 * Time: 2:39 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\fission\controllers\api;


use app\controllers\api\filters\LoginFilter;

class ApiController extends \app\controllers\api\ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class
            ],
        ]);
    }
}
