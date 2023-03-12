<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zjhj_bd_video_number_data".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $type
 * @property string $key
 * @property string $value
 * @property string $url
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class VideoNumberData extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video_number_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type', 'key', 'value', 'url'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'type' => 'Type',
            'key' => 'Key',
            'value' => 'Value',
            'url' => 'Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
