<?php

namespace app\plugins\teller\models;

use Yii;
use app\models\Store;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerSales;

/**
 * This is the model class for table "{{%teller_work_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $store_id 门店ID
 * @property string $start_time 上班时间
 * @property string $end_time 交班时间
 * @property int $cashier_id 收银员ID
 * @property string $status 交班状态pending 上班中|finish 交班完成
 * @property string $extra_attributes 交班详细信息
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 */
class TellerWorkLog extends \app\models\ModelActiveRecord
{
    const PENDING = 'pending';//上班中
    const FINISH = 'finish';// 交班完成


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teller_work_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'store_id', 'cashier_id', 'is_delete'], 'integer'],
            [['start_time', 'end_time', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['extra_attributes'], 'string'],
            [['status'], 'string', 'max' => 255],
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
            'store_id' => '门店ID',
            'start_time' => '上班时间',
            'end_time' => '交班时间',
            'cashier_id' => '收银员ID',
            'status' => '交班状态pending 上班中|finish 交班完成',
            'extra_attributes' => '交班详细信息',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getCashier()
    {
        return $this->hasOne(TellerCashier::className(), ['id' => 'cashier_id']);
    }

    public function getSales()
    {
        return $this->hasOne(TellerSales::className(), ['id' => 'sales_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }

    public function getStatisticsData($workLog)
    {
        $extra = json_decode($workLog->extra_attributes, true);

        $aggregate = [
            'total_aggregate' => 0,
            'wechat_aggregate' => 0,
            'alipay_aggregate' => 0,
            'cash_aggregate' => 0,
            'balance_aggregate' => 0,
            'pos_aggregate' => 0,
        ];

        foreach ($extra as $key => &$item) {
            
            if ($key == 'proceeds') {
                $aggregate['total_aggregate'] = price_format($aggregate['total_aggregate'] + $item['total_proceeds']);
                $aggregate['wechat_aggregate'] = price_format($aggregate['wechat_aggregate'] + $item['wechat_proceeds']);
                $aggregate['alipay_aggregate'] = price_format($aggregate['alipay_aggregate'] + $item['alipay_proceeds']);
                $aggregate['cash_aggregate'] = price_format($aggregate['cash_aggregate'] + $item['cash_proceeds']);
                $aggregate['balance_aggregate'] = price_format($aggregate['balance_aggregate'] + $item['balance_proceeds']);
                $aggregate['pos_aggregate'] = price_format($aggregate['pos_aggregate'] + $item['pos_proceeds']);
            }

            if ($key == 'recharge') {
                $aggregate['total_aggregate'] = price_format($aggregate['total_aggregate'] + $item['total_recharge']);
                $aggregate['wechat_aggregate'] = price_format($aggregate['wechat_aggregate'] + $item['wechat_recharge']);
                $aggregate['alipay_aggregate'] = price_format($aggregate['alipay_aggregate'] + $item['alipay_recharge']);
                $aggregate['cash_aggregate'] = price_format($aggregate['cash_aggregate'] + $item['cash_recharge']);
                $aggregate['pos_aggregate'] = price_format($aggregate['pos_aggregate'] + $item['pos_recharge']);
            }

            if ($key == 'refund') {
                $aggregate['total_aggregate'] = price_format($aggregate['total_aggregate'] - $item['total_refund']);
                $aggregate['wechat_aggregate'] = price_format($aggregate['wechat_aggregate'] - $item['wechat_refund']);
                $aggregate['alipay_aggregate'] = price_format($aggregate['alipay_aggregate'] - $item['alipay_refund']);
                $aggregate['cash_aggregate'] = price_format($aggregate['cash_aggregate'] - $item['cash_refund']);
                $aggregate['balance_aggregate'] = price_format($aggregate['balance_aggregate'] - $item['balance_refund']);
                $aggregate['pos_aggregate'] = price_format($aggregate['pos_aggregate'] - $item['pos_refund']);
            }
        }

        return [
            'aggregate' => $aggregate
        ];
    }
}
