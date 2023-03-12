<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2020 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\core\express\format;

use app\core\express\Interfaces\LogisticsStatus;
use app\core\express\Interfaces\WdConfigurationConstant;

class WdFormat extends BaseFormat implements WdConfigurationConstant, LogisticsStatus
{
    public function injection(array $data)
    {
        $state = $data['State'];
        list($status, $status_text) = $this->claimLogisticsStatus($state);
        return new self([
            self::F_STATE => $state,
            self::F_STSTUS => $status,
            self::F_STSTUS_TEXT => $status_text,
            self::F_STSTUS_LIST => array_map(function ($item) {
                return [
                    self::T_ITEM_DESC => $item['AcceptStation'],
                    self::T_ITEM_DATETIME => $item['AcceptTime'],
                    self::T_ITEM_MEMO => '',
                ];
            }, $data['Traces']),
        ]);
    }

    public function claimLogisticsStatus($status)
    {
        switch ($status) {
            case self::WD_STATUS_NO_RECORD:
                $status = self::LOGISTICS_STATUS_NO_RECORD;
                break;
            case self::WD_STATUS_COURIER_RECEIPT:
                $status = self::LOGISTICS_STATUS_COURIER_RECEIPT;
                break;
            case self::WD_STATUS_IN_TRANSIT:
                $status = self::LOGISTICS_STATUS_IN_TRANSIT;
                break;
            case self::WD_STATUS_SIGNING:
                $status = self::LOGISTICS_STATUS_SIGNED;
                break;
            case self::WD_STATUS_TIMEOUT:
                $status = self::LOGISTICS_STATUS_TIMEOUT;
                break;
            case self::WD_STATUS_DIFFICULT:
                $status = self::LOGISTICS_STATUS_TROUBLESOME;
                break;
            case self::WD_STATUS_REFUND:
                $status = self::LOGISTICS_STATUS_RETURN_RECEIPT;
                break;
            default:
                $status = self::LOGISTICS_STATUS_ERROR;
                break;
        }
        return [$status, self::LOGISTICS_STATUS_LABELS[$status]];
    }
}