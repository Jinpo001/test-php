<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/12
 * Time: 2:04 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\minishop\forms;

use app\plugins\wxapp\models\shop\ShopFactory;
use app\plugins\wxapp\Plugin;

class Model extends \app\models\Model
{
    /**
     * @var ShopFactory $shopService
     */
    public $shopService;

    public function init()
    {
        parent::init();
        /* @var Plugin $plugin */
        $plugin = \Yii::$app->plugin->getPlugin('wxapp');
        $this->shopService = $plugin->getShopService();
    }
}
