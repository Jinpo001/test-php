<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%account_permissions_group}}".
 *
 * @property int $id
 * @property string $name 权限套餐名称
 * @property string $permissions 权限
 * @property string $permissions_text
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class AccountPermissionsGroup extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%account_permissions_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['permissions', 'permissions_text'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['is_delete'], 'integer'],
            [['name'], 'string', 'max' => 65],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '权限套餐名称',
            'permissions' => '权限',
            'permissions_text' => 'Permissions Text',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
