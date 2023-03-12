<?php

namespace app\models;

use Yii;
use app\models\AccountPermissionsGroup;
use app\models\AdminInfo;

/**
 * This is the model class for table "{{%account_user_group}}".
 *
 * @property int $id
 * @property string $name 用户组名称
 * @property int $permissions_group_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class AccountUserGroup extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account_user_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['permissions_group_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '用户组名称',
            'permissions_group_id' => 'Permissions Group ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getPermissionsGroup()
    {
        return $this->hasOne(AccountPermissionsGroup::className(), ['id' => 'permissions_group_id']);
    }

    public function getAdminInfo()
    {
        return $this->hasMany(AdminInfo::className(), ['user_group_id' => 'id']);
    }
}
