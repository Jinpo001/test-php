<?php

namespace app\forms\common\order\send\city_service;

use CityService\Factory;
use app\forms\common\order\send\city_service\CityServiceForm;
use app\forms\common\order\send\city_service\ModelInterface;
use app\forms\common\order\send\city_service\ResponseInterface;

abstract class BaseCityService implements ResponseInterface, ModelInterface
{
    public $cityServiceForm;

    public function __construct(CityServiceForm $cityServiceForm)
    {
        $this->cityServiceForm = $cityServiceForm;
    }
}
