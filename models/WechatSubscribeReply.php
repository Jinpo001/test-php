<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%wechat_subscribe_reply}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $type 消息类型 0--文字 1--图片 2--语音 3--视频 4--图文
 * @property string $content 消息内容
 * @property string $title 图文消息时标题
 * @property string $picurl 图文消息时图片链接
 * @property string $url 图文消息时跳转链接，其他消息类型时媒体链接
 * @property string $media_id 图片，音频，视频消息时，临时素材id
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $status 消息使用的地方0--关注回复1--关键词回复2--菜单回复
 */
class WechatSubscribeReply extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_subscribe_reply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'type', 'is_delete', 'status'], 'integer'],
            [['picurl', 'url'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['content', 'title', 'media_id'], 'string', 'max' => 255],
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
            'type' => '消息类型 0--文字 1--图片 2--语音 3--视频 4--图文',
            'content' => '消息内容',
            'title' => '图文消息时标题',
            'picurl' => '图文消息时图片链接',
            'url' => '图文消息时跳转链接，其他消息类型时媒体链接',
            'media_id' => '图片，音频，视频消息时，临时素材id',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'status' => '消息使用的地方0--关注回复1--关键词回复2--菜单回复',
        ];
    }
}
