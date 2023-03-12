<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\mall;

use app\plugins\teller\forms\common\BaseWorkLogPrint;
use app\plugins\teller\models\TellerPrinterSetting;
use app\plugins\teller\models\TellerWorkLog;

class MallWorkLogPrint extends BaseWorkLogPrint
{
	public $work_log_id;
    public $print_id;

    public function rules()
    {
        return [
            [['work_log_id', 'print_id'], 'required'],
            [['work_log_id', 'print_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
    	return [
    		'work_log_id' => '交班记录ID',
            'print_id' => '打印机ID',
    	];
    }

	public function getWorkLog()
    {
    	if (!$this->validate()) {
            return $this->getErrorResponse();
        }

        $workLog = TellerWorkLog::find()->andWhere([
            'id' => $this->work_log_id,
            'mall_id' => \Yii::$app->mall->id,
        ])->with('cashier', 'store')->one();

        if (!$workLog) {
            throw new \Exception('交班记录不存在');
        }

        return $workLog;
    }

    public function getPrinterSetting()
    {
        $printerSetting = TellerPrinterSetting::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'status' => 1,
            'id' => $this->print_id
        ])->with('printer')->one();

        if (!$printerSetting) {
            throw new \Exception('请先添加打印设置');
        }

        return $printerSetting;
    }
}
