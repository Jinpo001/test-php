<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/5
 * Time: 1:57 下午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;


use app\helpers\CurlHelper;

class GoodsService extends BaseService
{
    public function getClient()
    {
        return CurlHelper::getInstance()->setPostType(CurlHelper::BODY);
    }

    /**
     * @param array $args
     * [
    'out_product_id' => '商家自定义商品ID',
    'title' => '标题',
    'path' => '绑定的小程序商品路径',
    'head_img' => '主图,多张,列表',
    'qualification_pics' => '商品资质图片',
    'desc_info' => ['desc' => '商品详情图文', 'imgs' => '商品详情图片'],
    'third_cat_id' => '第三级类目ID',
    'brand_id' => '品牌id',
    'skus' => '规格数据',
    ]
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/add_spu.html
     * 添加商品
     */
    public function add($args)
    {
        $api = "https://api.weixin.qq.com/shop/spu/add?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'out_product_id' => $args['out_product_id'],
            'title' => $args['title'],
            'path' => $args['path'],
            'head_img' => $args['head_img'],
            'qualification_pics' => $args['qualification_pics'],
            'desc_info' => $args['desc_info'],
            'third_cat_id' => $args['third_cat_id'],
            'brand_id' => $args['brand_id'],
            'skus' => $args['skus'],
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/del_spu.html
     * 删除商品 从初始值/上架/若干下架状态转换成逻辑删除（删除后不可恢复）
     */
    public function del($args)
    {
        $params = $this->getProductId($args);
        $api = "https://api.weixin.qq.com/shop/spu/del?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    protected function getProductId($args)
    {
        $params = [];
        if (isset($args['product_id'])) {
            $params['product_id'] = $args['product_id'];
        } elseif (isset($args['out_product_id'])) {
            $params['out_product_id'] = $args['out_product_id'];
        } else {
            throw new \Exception('缺少需要删除商品的id');
        }
        return $params;
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/get_spu.html
     * 获取商品
     */
    public function get($args)
    {
        $params = $this->getProductId($args);
        $params['need_edit_spu'] = $args['need_edit_spu'];
        $api = "https://api.weixin.qq.com/shop/spu/get?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/get_spu_list.html
     * 获取商品列表
     */
    public function getList($args)
    {
        $params = [
            'page' => $args['page'],
            'page_size' => $args['page_size'] >= 100 ? 100 : $args['page_size'],
            'need_edit_spu' => $args['need_edit_spu']
        ];
        if (isset($args['status'])) {
            $params['status'] = $args['status'];
        }
        if (isset($args['start_create_time']) && $args['start_create_time']) {
            $params['start_create_time'] = $args['start_create_time'];
            $params['end_create_time'] = $args['end_create_time'];
        } elseif (isset($args['start_update_time']) && $args['start_update_time']) {
            $params['start_update_time'] = $args['start_update_time'];
            $params['end_update_time'] = $args['end_update_time'];
        }
        $api = "https://api.weixin.qq.com/shop/spu/get_list?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/update_spu.html
     * 更新商品
     */
    public function update($args)
    {
        if (!isset($args['product_id'])) {
            throw new \Exception('缺少商品product_id参数，无法更新商品');
        }
        $api = "https://api.weixin.qq.com/shop/spu/update?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'out_product_id' => $args['out_product_id'],
            'product_id' => $args['product_id'],
            'title' => $args['title'],
            'path' => $args['path'],
            'head_img' => $args['head_img'],
            'qualification_pics' => $args['qualification_pics'],
            'desc_info' => $args['desc_info'],
            'third_cat_id' => $args['third_cat_id'],
            'brand_id' => $args['brand_id'],
            'skus' => $args['skus'],
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/update_spu_without_audit.html
     * 免审核更新商品
     */
    public function updateWithoutAudit($args)
    {
        $params = $this->getProductId($args);
        if (isset($args['path'])) {
            $params['path'] = $args['path'];
        }
        if (isset($args['skus'])) {
            $params['skus'] = $args['skus'];
        }
        $api = "https://api.weixin.qq.com/shop/spu/update_without_audit?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/listing_spu.html
     * 上架商品
     */
    public function listing($args)
    {
        $params = $this->getProductId($args);
        $api = "https://api.weixin.qq.com/shop/spu/listing?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/SPU/delisting_spu.html
     * 下架商品
     */
    public function delisting($args)
    {
        $params = $this->getProductId($args);
        $api = "https://api.weixin.qq.com/shop/spu/delisting?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], $params);
        return $this->getResult($res);
    }
}
