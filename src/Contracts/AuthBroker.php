<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

interface AuthBroker
{
    const USER_CHALLENGED = 'user_challenged';

    const DEVICE_VERIFIED = 'device_verified';

    const INVALID_TOKEN = 'invalid_token';

    const INVALID_FINGERPRINT = 'invalid_fingerprint';

    const EXPIRED_REQUEST = 'expired_request';
}