<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

interface AuthManager
{
    const NO_AUTH_REQUEST = 'no_auth_request';

    public function challenge(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip);

    public function verify(HasDeviceAuthorizations $user, $fingerprint, $token);

    public function setEventDispatcher(EventDispatcher $events);

    public function getEventDispatcher();
}