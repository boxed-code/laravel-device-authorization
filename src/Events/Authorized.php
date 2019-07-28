<?php

namespace BoxedCode\Laravel\Auth\Device\Events;

use BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization;

class Authorized
{
    /**
     * Th authorization instance.
     * 
     * @var \BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization
     */
    public $authorization;

    /**
     * Construct a new event instance.
     * 
     * @param \BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization $authorization
     */
    public function __construct(DeviceAuthorization $authorization)
    {
        $this->authorization = $authorization;
    }
}
