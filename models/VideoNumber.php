<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zjhj_bd_video_number".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $user_id
 * @property int $goods_id
 * @property string $media_id
 * @property string $msg_id
 * @property string $status
 * @property string $extra_attributes
 * @property string $created_at
 * @property string $updated_at
 */
class VideoNumber extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video_number}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'user_id', 'goods_id'], 'integer'],
            [['extra_attributes', 'status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['media_id', 'msg_id'], 'string', 'max' => 255],
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
            'user_id' => 'User ID',
            'goods_id' => 'Goods ID',
            'media_id' => 'Media ID',
            'msg_id' => 'Msg ID',
            'status' => 'Extra Attributes',
            'extra_attributes' => 'Url',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
