<?php

namespace app\forms\common\order\send\city_service;

use app\models\CityPreviewOrder;
use app\models\CityService;

interface ModelInterface
{
	// 驱动标识
	public function getDivers();

	// 驱动名称
	public function getName();

	// 驱动配置
	public function getConfig();

	// 预下单数据
    public function preOrderData($data): array;

    // 添加订单数据
    public function addOrderData(CityPreviewOrder $cityPreviewOrder): array;

    // 主动触发回调
    public function handleNotify(CityPreviewOrder $cityPreviewOrder);

    // 回调数据处理
    public function handleNotifyData(array $data);
}