<?php

namespace app\plugins\wxapp\models;

use Yii;

/**
 * This is the model class for table "{{%wxapp_wxminiprogram_audit}}".
 *
 * @property int $id ID
 * @property string $appid 小程序appid
 * @property string $auditid 审核编号
 * @property string $version
 * @property int $template_id 模板id
 * @property int $status 审核状态，其中0为审核成功，1为审核失败，2为审核中，3已提交审核, 4已发布
 * @property string $reason 当status=1，审核被拒绝时，返回的拒绝原因
 * @property string $created_at 提交审核时间
 * @property string $release_at
 * @property int $is_delete
 */
class WxappWxminiprogramAudit extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wxapp_wxminiprogram_audit}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['template_id', 'created_at'], 'required'],
            [['template_id', 'status', 'is_delete'], 'integer'],
            [['created_at', 'release_at'], 'safe'],
            [['appid', 'auditid', 'version'], 'string', 'max' => 45],
            [['reason'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'appid' => 'Appid',
            'auditid' => 'Auditid',
            'version' => 'Version',
            'template_id' => 'Template ID',
            'status' => 'Status',
            'reason' => 'Reason',
            'created_at' => 'Created At',
            'release_at' => 'Release At',
            'is_delete' => 'Is Delete',
        ];
    }
}
