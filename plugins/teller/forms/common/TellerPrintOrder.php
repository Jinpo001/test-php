<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/1/29
 * Time: 13:35
 * @copyright: ©2019 浙江禾匠信息科技
 * @link: http://www.zjhejiang.com
 */

namespace app\plugins\teller\forms\common;

use app\forms\common\prints\PrintOrder;
use app\plugins\teller\forms\common\TellerOrderTemplate;
use app\plugins\teller\models\TellerPrinterSetting;

class TellerPrintOrder extends PrintOrder
{
	public function getPrintSetting($order, $print_id)
    {
        $query = TellerPrinterSetting::find()->where([
            'mch_id' => $order->mch_id,
            'is_delete' => 0,
            'mall_id' => $order->mall_id,
            'status' => 1
        ])->with(['printer']);
        $print_id && $query->andWhere(['id' => $print_id]);

        $printSetting = $query->all();

        return $printSetting;
    }

    public function getTemplate($data, $item)
    {
        $template = new TellerOrderTemplate();
        $template->data = $data;
        $template->printer = $item;

        return $template;
    }
}
