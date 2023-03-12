<?php

namespace app\plugins\wxapp\models;

use Yii;

/**
 * This is the model class for table "{{%wxapp_fast_create}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 企业名称
 * @property string $code 企业代码
 * @property int $code_type 企业代码类型（1：统一社会信用代码， 2：组织机构代码，3：营业执照注册号）
 * @property string $legal_persona_wechat 法人微信
 * @property string $legal_persona_name 法人姓名
 * @property string $component_phone 第三方联系电话
 * @property string $md5 唯一标识
 * @property int $status
 * @property string $appid 创建小程序appid
 * @property string $auth_code 第三方授权码
 * @property string $updated_at
 * @property string $created_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class WxappFastCreate extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wxapp_fast_create}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'code', 'legal_persona_wechat', 'legal_persona_name', 'component_phone', 'md5', 'updated_at', 'created_at', 'deleted_at'], 'required'],
            [['mall_id', 'code_type', 'status', 'is_delete'], 'integer'],
            [['updated_at', 'created_at', 'deleted_at'], 'safe'],
            [['name', 'legal_persona_wechat', 'legal_persona_name', 'component_phone', 'md5', 'appid'], 'string', 'max' => 255],
            [['code', 'auth_code'], 'string', 'max' => 512],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '企业名称',
            'code' => '企业代码',
            'code_type' => 'Code Type',
            'legal_persona_wechat' => '法人微信',
            'legal_persona_name' => '法人姓名',
            'component_phone' => '第三方联系电话',
            'md5' => 'Md5',
            'status' => 'Status',
            'appid' => 'Appid',
            'auth_code' => 'Auth Code',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
