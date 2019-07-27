<?php

namespace BoxedCode\Laravel\Auth\Device\Events;

use BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization;

class Challenged
{
    public $authorization;

    public function __construct(DeviceAuthorization $authorization)
    {
        $this->authorization = $authorization;
    }
}
