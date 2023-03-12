<?php

namespace app\plugins\url_scheme\models;

use Yii;

/**
 * This is the model class for table "{{%url_scheme}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property int $expire_time 失效时间
 * @property int $is_expire 生成的scheme码类型， 到期失效：1， 永久有效：0。
 * @property string $link
 * @property string $url_scheme
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class UrlScheme extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%url_scheme}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'expire_time', 'is_expire', 'is_delete'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'link', 'url_scheme'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'expire_time' => '失效时间',
            'is_expire' => '生成的scheme码类型，
到期失效：1，
永久有效：0。',
            'link' => 'Link',
            'url_scheme' => 'Url Scheme',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
