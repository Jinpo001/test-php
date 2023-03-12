<?php

namespace app\forms\common\order\send\city_service;

interface ResponseInterface
{
	// 预下单成功数据处理
    public function preOrderResult(array $result);

    // 下单成功数据处理
    public function addOrderResult(array $result);
}