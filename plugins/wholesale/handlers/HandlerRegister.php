<?php

namespace app\plugins\wholesale\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderCreatedHandler::class,
            GoodsDestroyHandler::class
        ];
    }
}
