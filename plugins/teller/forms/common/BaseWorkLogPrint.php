<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: wxf
 */

namespace app\plugins\teller\forms\common;


use app\core\response\ApiCode;
use app\forms\common\prints\printer\FeiePrinter;
use app\forms\common\prints\printer\GpPrinter;
use app\forms\common\prints\printer\KdtPrinter;
use app\forms\common\prints\printer\YilianyunPrinter;
use app\models\Model;
use app\models\Printer;
use app\plugins\teller\forms\common\TellerFirstTemplate;
use app\plugins\teller\models\TellerPrinterSetting;
use app\plugins\teller\models\TellerWorkLog;

abstract class BaseWorkLogPrint extends Model
{
    public function print()
    {
        try {
            $printerSetting = $this->getPrinterSetting();

            $workLog = $this->getWorkLog();

            $extra = json_decode($workLog->extra_attributes, true);
            $statisticsData = $workLog->getStatisticsData($workLog);
            foreach ($extra as $key => &$item) {
                foreach ($item as $key2 => &$value) {
                    if ($key2 != 'total_order') {
                        $value = price_format($value);
                    }
                }
            }
            
            $extra = array_merge($extra, $statisticsData);
            $data = array_merge([
                'store_name' => $workLog->store->name,
                'cashier_name' => $workLog->cashier->number . '  ' . $workLog->cashier->user->nickname,
                'start_time' => $workLog->start_time,
                'end_time' => $workLog->end_time ?: date('Y-m-d H:i:s', time()),
            ], $extra);

            $printer = $printerSetting->printer;
            $config = \Yii::$app->serializer->decode($printer->setting);
            $limit = 0;
            switch ($printer->type) {
                case Printer::P_360_KDT2:
                    \Yii::warning('365kdt打印');
                    $printer = new KdtPrinter($config);
                    $limit = 5000;
                    break;
                case Printer::P_FEIE:
                    \Yii::warning('飞鹅打印');
                    $printer = new FeiePrinter($config);
                    $limit = 5000;
                    break;
                case Printer::P_YILIANYUN_K4:
                    \Yii::warning('易联云打印');
                    $printer = new YilianyunPrinter($config);
                    break;
                case Printer::P_GAINSCHA_GP:
                    \Yii::warning('佳博打印');
                    $config['orderNo'] = 'teller' . time();
                    $printer = new GpPrinter($config);
                    break;
                default:
                    \Yii::warning('未知的打印机设置');
                    $printer = null;
            }
            try {
                if (!$printer) {
                    throw new \Exception('未知打印机设置');
                }
                $template = new TellerFirstTemplate();
                $template->data = $data;
                $template->printer = $printerSetting;
                $printer->print($template->getContentByArray(), $limit);
                $count++;
            } catch (\Exception $exception) {
                \Yii::error($exception);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '打印成功'
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage(),
                'line' => $exception->getLine()
            ];
        }
    }

    abstract function getWorkLog();

    public function getPrinterSetting()
    {
        $setting = (new CommonTellerSetting())->search();

        $printerSetting = TellerPrinterSetting::find()->andWhere([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => \Yii::$app->user->identity->mch_id,
            'is_delete' => 0,
            'status' => 1,
            'id' => $setting['shifts_print']
        ])->with('printer')->one();

        if (!$printerSetting) {
            throw new \Exception('请先添加打印设置');
        }

        return $printerSetting;
    }
}
