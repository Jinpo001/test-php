<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\fission\forms\receive\basic;

class Cash extends BaseAbstract implements Base
{
    public function exchange(&$message,&$log)
    {
        try {
            return true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return false;
        }
    }
}
