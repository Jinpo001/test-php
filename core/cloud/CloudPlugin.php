<?php
/**
 * @copyright ©2018 浙江禾匠信息科技
 * @author Lu Wei
 * @link http://www.zjhejiang.com/
 * Created by IntelliJ IDEA
 * Date Time: 2019/1/4 18:20:00
 */


namespace app\core\cloud;


class CloudPlugin extends CloudBase
{
    public $classVersion = '4.2.10';

    /**
     * @param array $args 查询参数
     * @return array ['list'=>[],'pagination'=>[]]
     * @throws CloudException
     */
    public function getList($args = [])
    {
        return [
            'list' => [],
        ];
    }

    /**
     * @param $args
     * @return mixed
     * @throws CloudException
     */
    public function getDetail($args)
    {
        throw new CloudException('插件不存在', 0, null, null);
    }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function createOrder($id)
    {
        return $this->httpPost('/mall/plugin/create-order', [], [
            'id' => $id,
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws CloudException
     */
    public function install($id)
    {
        return $this->httpGet('/mall/plugin/install', ['id' => $id]);
    }

    /**
     * 远程插件和分类相关数据
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function getPluginData()
    {
        return $this->httpGet('/mall/plugin/plugin-data');
    }
}
