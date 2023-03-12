<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%core_file}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $file_name 文件名称
 * @property string $percent 下载进度
 * @property int $status 是否完成
 * @property int $is_delete
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $user_id 用户ID
 */
class CoreFile extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_file}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'mall_id', 'mch_id', 'status', 'is_delete', 'user_id'], 'integer'],
            [['percent'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['file_name'], 'string', 'max' => 255],
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
            'file_name' => '文件名称',
            'percent' => '下载进度',
            'status' => '是否完成',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'user_id' => '用户ID',
        ];
    }


    public function getStatusText($item) 
    {
        switch ($item->status) {
            case 0:
                return '生成中';
                break;
            case 1:
                return '已生成';
                break;
            case 2:
                return '生成异常';
                break;
            
            default:
                return '未知';
                break;
        }
    }
}
