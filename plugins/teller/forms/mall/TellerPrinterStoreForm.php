<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
namespace app\plugins\teller\forms\mall;

use app\core\response\ApiCode;
use app\models\Model;
use app\models\Printer;
use app\models\Store;
use app\plugins\teller\models\TellerPrinterSetting;
use yii\base\DynamicModel;

class TellerPrinterStoreForm extends Model
{
    public $printer_id;
    public $status;
    public $store_id;
    public $big;
    public $show_type;

    public function rules()
    {
        return [
            [['printer_id', 'status', 'store_id', 'show_type'], 'required'],
            [['printer_id', 'status', 'store_id', 'big'], 'integer'],
            [['show_type'], 'trim'],
            [['show_type'], 'string'],
            [['big'], 'default', 'value' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'printer_id' => '打印机ID',
            'show_type' => '显示方式',
            'status' => '是否启用',
            'store_id' => '门店',
            'big' => '倍数',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        try {
            $this->checkData();

            $printer = new TellerPrinterSetting();
            $printer->mall_id = \Yii::$app->mall->id;
            $printer->mch_id = \Yii::$app->user->identity->mch_id;
            $printer->printer_id = $this->printer_id;
            $printer->store_id = $this->store_id;
            $printer->big = $this->big;
            $printer->status = $this->status;
            $printer->show_type = $this->show_type;
            $printer->type = json_encode([
                'order' => 1,
                'pay' => 1,
                'confirm' => 1
            ]);
            $printer->order_send_type = json_encode([
                'express' => 1,
                'offline' => 1,
                'city' => 1,
                'auto' => 1
            ]);
            $res = $printer->save();

            if (!$res) {
                throw new \Exception($this->getErrorMsg($printer));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];

        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    private function checkData()
    {
        $showType = json_decode($this->show_type, true);
        if ($this->validateShowType($showType['attr'], $showType['goods_no'])) {
            throw new \Exception('数据不合法');
        }

        $store = Store::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->store_id,
            'is_delete' => 0
        ])->one();
        
        if (!$store) {
            throw new \Exception('门店不存在');
        }

        $printer = Printer::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'id' => $this->printer_id,
            'is_delete' => 0
        ])->one();

        if (!$printer) {
            throw new \Exception('打印机不存在');
        }
    }

    private function validateShowType($attr, $goods_no)
    {
        $model = DynamicModel::validateData(compact('attr', 'goods_no'), [
            [['attr', 'goods_no'], 'required'],
        ]);
        return $model->hasErrors();
    }
}
