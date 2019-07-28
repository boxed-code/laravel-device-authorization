<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;

interface AuthBroker
{
    const USER_CHALLENGED = 'user_challenged';

    const DEVICE_VERIFIED = 'device_verified';

    const DEVICE_AUTHORIZED = 'device_verified';

    const INVALID_TOKEN = 'invalid_token';

    const INVALID_FINGERPRINT = 'invalid_fingerprint';

    const EXPIRED_REQUEST = 'expired_request';

    const DEVICE_ALREADY_AUTHORIZED = 'device_already_authorized';

    public function challenge(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip);

    public function verify(HasDeviceAuthorizations $user, $fingerprint, $token);

    public function authorize(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip);

    public function setEventDispatcher(EventDispatcher $events);

    public function getEventDispatcher();
}