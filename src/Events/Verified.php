<?php

namespace BoxedCode\Laravel\Auth\Device\Events;

use BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization;

class Verified
{
    public $authorization;

    public function __construct(DeviceAuthorization $authorization)
    {
        $this->authorization = $authorization;
    }
}
