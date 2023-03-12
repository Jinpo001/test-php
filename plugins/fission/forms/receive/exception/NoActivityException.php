<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\fission\forms\receive\exception;


class NoActivityException extends \Exception
{
    public $activity;
    public function __construct($message, $activity)
    {
        $this->activity = $activity;
        parent::__construct($message);
    }

    public function getActivity()
    {
        return $this->activity;
    }
}