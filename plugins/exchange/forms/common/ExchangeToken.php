<?php

/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\plugins\exchange\forms\common;

class ExchangeToken
{
    //加密
    public static function encryption($library_id)
    {
        $library_id or die('LIBRARY ERROR');
        $time = date('Y-m-d H:i:s');
        $timekey = (new \DateTime($time))->format('Y2H0m2i0ds');
        $token = md5(sha1(md5($timekey . $library_id)));

        return [
            'type' => 'auto',
            'created_at' => $time,
            'token' => $token,
            'library_id' => $library_id,
        ];
    }

    public static function valid($library_id, $time, $token)
    {
        $timekey = (new \DateTime($time))->format('Y2H0m2i0ds');
        $v_token = md5(sha1(md5($timekey . $library_id)));
        return substr_compare($token, $v_token, 0) === 0;
    }

    public function __construct(array $attributes = [])
    {
        throw new \Exception('禁止实例化');
    }

    public function __sleep()
    {
        throw new \Exception('禁止序列化');
    }

    public function __invoke()
    {
        throw new \Exception('禁止');
    }
}
