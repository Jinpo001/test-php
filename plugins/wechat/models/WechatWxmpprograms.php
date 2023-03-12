<?php

namespace app\plugins\wechat\models;

use app\forms\open3rd\UpdateToken;
use Yii;

/**
 * This is the model class for table "{{%wechat_wxmpprograms}}".
 *
 * @property int $id ID
 * @property int $mall_id 商城id
 * @property string $nick_name 公众号名称
 * @property string $token 平台生成的token值
 * @property string $head_img 公众号头像
 * @property int $verify_type_info 授权方认证类型，-1代表未认证，0代表微信认证
 * @property int $is_show 是否显示，0显示，1隐藏
 * @property string $user_name 原始ID
 * @property string $qrcode_url 二维码图片的URL
 * @property string $business_info json格式。用以了解以下功能的开通状况（0代表未开通，1代表已开通）： open_store:是否开通微信门店功能 open_scan:是否开通微信扫商品功能 open_pay:是否开通微信支付功能 open_card:是否开通微信卡券功能 open_shake:是否开通微信摇一摇功能
 * @property int $idc idc
 * @property string $principal_name 公众号的主体名称
 * @property string $signature 帐号介绍
 * @property string $miniprograminfo json格式。判断是否为小程序类型授权，包含network小程序已设置的各个服务器域名
 * @property string $func_info json格式。权限集列表，ID为17到19时分别代表： 17.帐号管理权限 18.开发管理权限 19.客服消息管理权限 请注意： 1）该字段的返回不会考虑小程序是否具备该权限集的权限（因为可能部分具备）。
 * @property string $authorizer_appid 公众号appid
 * @property string $authorizer_access_token 授权方接口调用凭据（在授权的公众号或小程序具备API权限时，才有此返回值），也简称为令牌
 * @property int $authorizer_expires refresh有效期
 * @property string $authorizer_refresh_token 接口调用凭据刷新令牌
 * @property string $created_at 授权时间
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class WechatWxmpprograms extends \app\models\ModelActiveRecord
{
    use UpdateToken;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_wxmpprograms}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'verify_type_info', 'is_show', 'idc', 'authorizer_expires', 'is_delete'], 'integer'],
            [['func_info'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['nick_name', 'token', 'user_name', 'principal_name', 'authorizer_appid'], 'string', 'max' => 45],
            [['head_img', 'signature', 'miniprograminfo', 'authorizer_access_token', 'authorizer_refresh_token'], 'string', 'max' => 255],
            [['qrcode_url', 'business_info'], 'string', 'max' => 2048],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'nick_name' => 'Nick Name',
            'token' => 'Token',
            'head_img' => 'Head Img',
            'verify_type_info' => 'Verify Type Info',
            'is_show' => 'Is Show',
            'user_name' => 'User Name',
            'qrcode_url' => 'Qrcode Url',
            'business_info' => 'Business Info',
            'idc' => 'Idc',
            'principal_name' => 'Principal Name',
            'signature' => 'Signature',
            'miniprograminfo' => 'Miniprograminfo',
            'func_info' => 'Func Info',
            'authorizer_appid' => 'Authorizer Appid',
            'authorizer_access_token' => 'Authorizer Access Token',
            'authorizer_expires' => 'Authorizer Expires',
            'authorizer_refresh_token' => 'Authorizer Refresh Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
