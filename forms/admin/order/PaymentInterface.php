<?php

namespace app\forms\admin\order;

interface PaymentInterface
{
    public function getService();
    
    public function getNotifyUrl();
}