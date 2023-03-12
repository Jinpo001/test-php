<?php
/**
 * Created by PhpStorm
 * User: 风哀伤
 * Date: 2021/3/5
 * Time: 9:37 上午
 * @copyright: ©2020 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\wxapp\models\shop;

use app\helpers\CurlHelper;

class RegisterService extends BaseService
{
    public function getClient()
    {
        return CurlHelper::getInstance()->setPostType(CurlHelper::BODY);
    }

    /**
     * @param array $args ['action_type' => '接入类型, 默认0, 0:接入, 1:打开ticket校验, 2:关闭ticket校验']
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/enter/enter_apply.html
     * 接入申请/变更
     */
    public function apply($args)
    {
        $api = "https://api.weixin.qq.com/shop/register/apply?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'action_type' => $args['action_type']
        ]);
        return $this->getResult($res);
    }

    /**
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/enter/enter_check.html
     * 查询接入审核结果
     */
    public function check()
    {
        $api = "https://api.weixin.qq.com/shop/register/check?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api);
        return $this->getResult($res);
    }

    /**
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/cat/get_children_cateogry.html
     * 获取商品类目
     */
    public function getCat()
    {
        $api = "https://api.weixin.qq.com/shop/cat/get?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * [
    'license' => $args['license'],
    'brand_info' => [
    'brand_audit_type' => '认证审核类型 1--国内品牌申请-R标 2--国内品牌申请-TM标 3--海外品牌申请-R标 4--海外品牌申请-TM标',
    'trademark_type' => '商标分类 "1"--第1类 ～ "45"--第45类',
    'brand_management_type' => '选择品牌经营类型 1--自有品牌 2--代理品牌 3--无品牌',
    'commodity_origin_type' => '商品产地是否进口 1--是 2--否',
    'brand_wording' => '商标/品牌词',
    'sale_authorization' => '销售授权书（如商持人为自然人，还需提供有其签名的身份证正反面扫描件)，图片url/media_id',
    'trademark_registration_certificate' => '商标注册证书，图片url/media_id',
    'trademark_change_certificate' => '商标变更证明，图片url/media_id',
    'trademark_registrant' => '商标注册人姓名',
    'trademark_registrant_nu' => '商标注册号/申请号',
    'trademark_authorization_period' => '商标有效期，yyyy-MM-dd HH:mm:ss',
    'trademark_registration_application' => '商标注册申请受理通知书，图片url/media_id',
    'trademark_applicant' => '商标申请人姓名',
    'trademark_application_time' => '商标申请时间, yyyy-MM-dd HH:mm:ss',
    'imported_goods_form' => '中华人民共和国海关进口货物报关单，图片url/media_id',
    ]
    ]
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/audit/audit_brand.html
     * 品牌审核
     */
    public function auditBrand($args)
    {
        $api = "https://api.weixin.qq.com/shop/audit/audit_brand?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'audit_req' => [
                'license' => $args['license'],
                'brand_info' => [
                    'brand_audit_type' => $args['brand_audit_type'],
                    'trademark_type' => $args['trademark_type'],
                    'brand_management_type' => $args['brand_management_type'],
                    'commodity_origin_type' => $args['commodity_origin_type'],
                    'brand_wording' => $args['brand_wording'],
                    'sale_authorization' => $args['sale_authorization'],
                    'trademark_registration_certificate' => $args['trademark_registration_certificate'],
                    'trademark_change_certificate' => $args['trademark_change_certificate'],
                    'trademark_registrant' => $args['trademark_registrant'],
                    'trademark_registrant_nu' => $args['trademark_registrant_nu'],
                    'trademark_authorization_period' => $args['trademark_authorization_period'],
                    'trademark_registration_application' => $args['trademark_registration_application'],
                    'trademark_applicant' => $args['trademark_applicant'],
                    'trademark_application_time' => $args['trademark_application_time'],
                    'imported_goods_form' => $args['imported_goods_form'],
                ]
            ]
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/audit/audit_category.html
     * 类目审核
     */
    public function auditCategory($args)
    {
        $api = "https://api.weixin.qq.com/shop/audit/audit_category?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'audit_req' => [
                'license' => $args['license'],
                'category_info' => [
                    'level1' => $args['level1'],
                    'level2' => $args['level2'],
                    'level3' => $args['level3'],
                    'certificate' => $args['certificate'],
                ]
            ]
        ]);
        return $this->getResult($res);
    }

    /**
     * @param array $args ['req_type' => '1--类目 2--品牌']
     * @return mixed
     * @throws \Exception
     * https://developers.weixin.qq.com/miniprogram/dev/framework/ministore/minishopopencomponent2/API/audit/get_miniapp_certificate.html
     * 获取小程序资质
     */
    public function getMiniappCertificate($args)
    {
        $api = "https://api.weixin.qq.com/shop/audit/get_miniapp_certificate?access_token={$this->accessToken}";
        $res = $this->getClient()->httpPost($api, [], [
            'req_type' => $args['req_type']
        ]);
        return $this->getResult($res);
    }
}
