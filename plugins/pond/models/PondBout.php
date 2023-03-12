<?php

namespace app\plugins\pond\models;

use app\models\ModelActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%pond_bout}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $bout
 * @property string $updated_at
 */
class PondBout extends ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pond_bout}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'bout'], 'integer'],
            [['updated_at'], 'safe'],
            [['user_id'], 'unique'],
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
            'user_id' => 'User ID',
            'bout' => 'Bout',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getBout()
    {
        return self::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'user_id' => \Yii::$app->user->id
        ])->one();
    }
}
