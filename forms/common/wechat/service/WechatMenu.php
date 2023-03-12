<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/2/22
 * Time: 5:28 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\forms\common\wechat\service;

use app\helpers\CurlHelper;

class WechatMenu extends BaseService
{
    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * 创建菜单
     * https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Creating_Custom-Defined_Menu.html
     */
    public function create($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$this->accessToken}";
        $res = $this->getClient()->setPostType(CurlHelper::BODY)->httpPost($api, [], $args);
        return $this->getResult($res);
    }

    /**
     * @return mixed
     * @throws \Exception
     * 查询菜单
     * https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Querying_Custom_Menus.html
     */
    public function getMenuInfo()
    {
        $api = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token={$this->accessToken}";
        $res = $this->getClient()->httpGet($api);
        return $this->getResult($res);
    }

    /**
     * @return mixed
     * @throws \Exception
     * 删除菜单
     * https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Deleting_Custom-Defined_Menu.html
     */
    public function deleteMenu()
    {
        $api = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$this->accessToken}";
        $res = $this->getClient()->httpGet($api);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * 创建个性化菜单
     * https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
     */
    public function addConditional($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token={$this->accessToken}";
        $res = $this->getClient()->setPostType(CurlHelper::BODY)->httpPost($api, [], $args);
        return $this->getResult($res);
    }

    /**
     * @param array $args ["menuid" => 495420948]
     * @return mixed
     * @throws \Exception
     * 删除个性化菜单
     */
    public function delConditional($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token={$this->accessToken}";
        $res = $this->getClient()->setPostType(CurlHelper::BODY)->httpPost($api, [], $args);
        return $this->getResult($res);
    }

    /**
     * @param array $args ['user_id' => openid或者微信号]
     * @return mixed
     * @throws \Exception
     * 测试个性化菜单匹配结果
     * https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Personalized_menu_interface.html
     */
    public function tryMatch($args)
    {
        $api = "https://api.weixin.qq.com/cgi-bin/menu/trymatch?access_token={$this->accessToken}";
        $res = $this->getClient()->setPostType(CurlHelper::BODY)->httpPost($api, [], $args);
        return $this->getResult($res);
    }

    /**
     * @return mixed
     * @throws \Exception
     * 获取自定义菜单配置
     * https://developers.weixin.qq.com/doc/offiaccount/Custom_Menus/Getting_Custom_Menu_Configurations.html
     */
    public function getCustomMenu()
    {
        $api = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$this->accessToken}";
        $res = $this->getClient()->httpGet($api);
        return $this->getResult($res);
    }
}
