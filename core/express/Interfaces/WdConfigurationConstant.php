<?php

namespace app\core\express\Interfaces;

interface WdConfigurationConstant
{
    public const PROVIDER_NAME = 'wd';

    public const LOGISTICS_COM_CODE_URL = '';     //智能单号识别

    public const SELECT_URL = 'https://wdexpress.market.alicloudapi.com/gxali';

    public const SUCCESS_STATUS = 200;

    public const WD_STATUS_ERROR = -1;

    public const WD_STATUS_NO_RECORD = 0;

    public const WD_STATUS_COURIER_RECEIPT = 1;

    public const WD_STATUS_IN_TRANSIT = 2;

    public const WD_STATUS_SIGNING = 3;

    public const WD_STATUS_TIMEOUT = 4;

    public const WD_STATUS_DIFFICULT = 5;

    public const WD_STATUS_REFUND = 6;

    const WD_STATUS_LABELS = [
        self::WD_STATUS_ERROR => '单号或快递公司代码错误',
        self::WD_STATUS_NO_RECORD => '暂无轨迹',
        self::WD_STATUS_COURIER_RECEIPT => '快递收件',
        self::WD_STATUS_IN_TRANSIT => '在途中',
        self::WD_STATUS_SIGNING => '签收',
        self::WD_STATUS_TIMEOUT => '问题件',
        self::WD_STATUS_DIFFICULT => '疑难件',
        self::WD_STATUS_REFUND => '退件签收',
    ];
}
