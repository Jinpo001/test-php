<?php

namespace app\plugins\teller\models;

use Yii;
use app\models\Printer;
use app\models\Store;

/**
 * This is the model class for table "{{%teller_printer_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $store_id
 * @property int $printer_id 打印机id
 * @property int $status 0关闭 1启用
 * @property string $show_type attr 规格 goods_no 货号 form_data 下单表单
 * @property int $big 倍数
 * @property int $is_delete 删除
 * @property string $type
 * @property string $order_send_type
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class TellerPrinterSetting extends \app\models\ModelActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teller_printer_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'printer_id', 'show_type', 'created_at', 'updated_at', 'deleted_at', 'type', 'order_send_type'], 'required'],
            [['mall_id', 'mch_id', 'store_id', 'printer_id', 'status', 'big', 'is_delete'], 'integer'],
            [['show_type', 'type', 'order_send_type'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'store_id' => 'Store ID',
            'printer_id' => '打印机id',
            'status' => '0关闭 1启用',
            'show_type' => 'attr 规格 goods_no 货号 form_data 下单表单',
            'big' => '倍数',
            'is_delete' => '删除',
            'type' => '打印类型',
            'order_send_type' => '发货类型',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }

    public function getPrinter()
    {
        return $this->hasOne(Printer::className(), ['id' => 'printer_id']);
    }
}
