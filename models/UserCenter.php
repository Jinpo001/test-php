<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_center}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property resource $config
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property string $name
 * @property int $is_recycle
 * @property string $platform
 */
class UserCenter extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_center}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'config'], 'required'],
            [['mall_id', 'is_delete', 'is_recycle'], 'integer'],
            [['config'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'platform'], 'string', 'max' => 255],
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
            'config' => 'Config',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'name' => 'Name',
            'is_recycle' => 'Is Recycle',
            'platform' => '所属平台',
        ];
    }
}
