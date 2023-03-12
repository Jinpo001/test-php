<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/1/28
 * Time: 9:52 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\helpers;

use yii\base\BaseObject;

class EncryptHelper extends BaseObject
{
    public $key = 'https://www.zjhejiang.com';

    /**
     * @param $data
     * @param $key
     * @param string $string
     * @return string
     * 异或运算
     */
    public function getXOR($data, $key, $string = '')
    {
        $len = strlen($data);
        $len2 = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            $j = $i % $len2;
            $string .= ($data[$i]) ^ ($key[$j]);
        }
        return $string;
    }

    /**
     * @param $data
     * @return string
     * 简单加密
     */
    public static function encrypt($data)
    {
        $class = new self();
        $xorData = $class->getXOR($data, $class->key);
        return base64_encode($class->key . $xorData);
    }

    /**
     * @param $data
     * 简单解密
     */
    public static function decrypt($data)
    {
        $class = new self();
        $data = base64_decode($data);
        $len = strlen($class->key);
        $key = substr($data, 0, $len);
        if ($key !== $class->key) {
            throw new \Exception('错误的字符串，无法解密');
        }
        $xorData = substr($data, $len);
        return $class->getXOR($xorData, $class->key);
    }
}
