<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;

interface AuthBroker
{
    /**
     * Constant representing the user has been challenged.
     */
    const USER_CHALLENGED = 'user_challenged';

    /**
     * Constant representing a user not being allowed to authorize devices.
     */
    const USER_CANNOT_AUTHORIZE_DEVICES = 'user_cannot_authorize_devices';

    /**
     * Constant representing the device has been authorized.
     */
    const DEVICE_AUTHORIZED = 'device_authorized';

    /**
     * Constant representing the token presented was invalid.
     */
    const INVALID_TOKEN = 'invalid_token';

    /**
     * Constant representing the fingerprint presented was invalid.
     */
    const INVALID_FINGERPRINT = 'invalid_fingerprint';

    /**
     * Constant representing that the request presented has expired.
     */
    const EXPIRED_REQUEST = 'expired_request';

    /**
     * Constant representing that the device has already been authorized.
     */
    const DEVICE_ALREADY_AUTHORIZED = 'device_already_authorized';

    /**
     * Challenge the user to verify themselves before authorizing the device.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint
     * @param  string $browser    
     * @param  string $ip         
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse
     */
    public function challenge(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip);

    /**
     * Verify the presented token is valid, the fingerprint matches it 
     * and authorize the fingerprint.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint
     * @param  string $token 
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse
     */
    public function verifyAndAuthorize(HasDeviceAuthorizations $user, $fingerprint, $token);

    /**
     * Authorize the given fingerprint.
     * 
     * @param  HasDeviceAuthorizations $user        
     * @param  string $fingerprint 
     * @param  string $browser     
     * @param  string $ip          
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse                        
     */
    public function authorize(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip);

    /**
     * Set the event dispatcher.
     * 
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function setEventDispatcher(EventDispatcher $events);

    /**
     * Get the event dispatcher.
     * 
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher();
}