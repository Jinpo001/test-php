<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\web;

use app\plugins\teller\forms\common\BaseWorkLogPrint;
use app\plugins\teller\models\TellerCashier;
use app\plugins\teller\models\TellerWorkLog;

class WebWorkLogPrint extends BaseWorkLogPrint
{

	public function getWorkLog()
    {
    	$cashier = TellerCashier::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'user_id' => \Yii::$app->user->id
        ])->one();

        if (!$cashier) {
            throw new \Exception('收银员不存在');
        }

    	$workLog = TellerWorkLog::find()->andWhere([
            'mall_id' => $cashier->mall_id,
            'mch_id' => $cashier->mch_id,
            'cashier_id' => $cashier->id,
            'is_delete' => 0,
            'status' => TellerWorkLog::PENDING
        ])->one();

        if (!$workLog) {
            throw new \Exception('无上班记录,无法打印');
        }

        $workLog = TellerWorkLog::find()->andWhere([
            'id' => $workLog->id,
            'mall_id' => \Yii::$app->mall->id,
        ])->with('cashier', 'store')->one();

        if (!$workLog) {
            throw new \Exception('交班记录不存在');
        }

        return $workLog;
    }
}
