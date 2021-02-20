<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\AuthBrokerResponse;
use BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker as BrokerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthBroker implements BrokerContract
{
    /**
     * The configuration array.
     * 
     * @var array
     */
    protected $config;

    /**
     * The event dispatcher instance.
     * 
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new broker instance.
     * 
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Send a challenge to the user with a verification link.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint 
     * @param  string $browser     
     * @param  string $ip          
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse
     */
    public function challenge(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip)
    {
        // Flush all other pending authorizations for this user.
        $user->deviceAuthorizations()->pending()->delete();

        // Check that the user can authorize devices.
        if (!$user->canAuthorizeDevice()) {
            return $this->respond(static::USER_CANNOT_AUTHORIZE_DEVICES);
        }

        // Check that the device is not already authorized.
        if ($authorization = $this->findExistingVerifiedAuthorization($user, $fingerprint)) {
            return $this->respond(static::DEVICE_ALREADY_AUTHORIZED, [
                'authorization' => $authorization
            ]);
        }

        // Create a new authorization.
        $authorization = $this->newAuthorization($user, $fingerprint, $browser, $ip);

        // Send the request and verification token        
        $user->notify(new $this->config['notification']($authorization->verify_token, $browser, $ip));

        $this->event(new Events\Challenged($authorization));

        return $this->respond(static::USER_CHALLENGED, [
            'authorization' => $authorization
        ]);
    }   

    /**
     * Verify the challenge and authorize the user.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint
     * @param  string $token      
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse
     */
    public function verifyAndAuthorize(HasDeviceAuthorizations $user, $fingerprint, $token)
    {
        // Verify the token.
        if (empty($token) || !($authorization = $user->deviceAuthorizations()->pending($token)->first())) {
            return $this->respond(static::INVALID_TOKEN);
        }

        // Check that the request has not expired.
        $requestLifetime = $this->config['lifetimes']['request'];
        if ($authorization->created_at->lessThan(now()->subSeconds($requestLifetime))) {
            return $this->response(static::EXPIRED_REQUEST);
        }

        // Verify the fingerprints match.
        $algorithm = $this->config['fingerprints']['algorithm'];

        if (hash($algorithm, $fingerprint) !== $authorization->fingerprint) {
            return $this->respond(static::INVALID_FINGERPRINT);
        }

        $this->event(new Events\Verified($authorization));

        // Mark the authorization as verified
        $authorization->fill(['verified_at' => now()])->save();

        $this->event(new Events\Authorized($authorization));

        return $this->respond(static::DEVICE_AUTHORIZED, [
            'authorization' => $authorization
        ]);
    }

    /**
     * Authorize a device without verification.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint 
     * @param  string $browser     
     * @param  string $ip          
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse
     */
    public function authorize(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip)
    {
        // Check that the user can authorize devices.
        if (!$user->canAuthorizeDevice()) {
            return $this->respond(static::USER_CANNOT_AUTHORIZE_DEVICES);
        }

        // Check the device is not already verified.
        if ($authorization = $this->findExistingVerifiedAuthorization($user, $fingerprint)) {
            return $this->respond(static::DEVICE_ALREADY_AUTHORIZED, [
                'authorization' => $authorization
            ]);
        }

        // Create a new verified authorization.
        $authorization = $this->newAuthorization(
            $user, $fingerprint, $browser, $ip, $verified_at = now()
        );

        return $this->respond(static::DEVICE_AUTHORIZED, [
            'authorization' => $authorization
        ]);
    }

    /**
     * Set the event dispatcher.
     * 
     * @param EventDispatcher $events
     */
    public function setEventDispatcher(EventDispatcher $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Get the event dispatcher.
     * 
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Try to dispatch an event.
     *
     * @return void
     */
    protected function event()
    {
        if ($this->events) {
            call_user_func_array(
                [$this->events, 'dispatch'], 
                func_get_args()
            );
        }
    }

    /**
     * Generate a new verification token.
     * 
     * @return string
     */
    protected function newVerifyToken()
    {
        return Str::random(40);
    }

    /**
     * Create a new authorization record.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint 
     * @param  string $browser     
     * @param  string $ip          
     * @param  DateTime|null $verified_at
     * @return \BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization
     */
    protected function newAuthorization(HasDeviceAuthorizations $user, 
                                        $fingerprint, 
                                        $browser, 
                                        $ip, 
                                        $verified_at = null
    ) {
        $algorithm = $this->config['fingerprints']['algorithm'];

        $fingerprintHash = hash($algorithm, $fingerprint);

        // Create the authorizations
        return $user->deviceAuthorizations()->create([
            'uuid' => Str::uuid(),
            'fingerprint' => $fingerprintHash,
            'browser' => $browser,
            'ip' => $ip,
            'verify_token' => $token = $this->newVerifyToken(),
            'verified_at' => $verified_at,
        ]);
    }

    /**
     * Find an existing verified verification by fingerprint.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations $user
     * @param  string $fingerprint
     * @return \BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization
     */
    protected function findExistingVerifiedAuthorization(HasDeviceAuthorizations $user, $fingerprint)
    {
        $algorithm = $this->config['fingerprints']['algorithm'];

        $fingerprintHash = hash($algorithm, $fingerprint);

        if ($authorization = $user->deviceAuthorizations()->verifiedFingerprint($fingerprintHash)->first()) {
            return $authorization;
        }

        return false;
    }

    /**
     * Create a new broker response instance.
     * 
     * @param  string $outcome
     * @param  array  $payload
     * @return \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse
     */
    protected function respond($outcome, array $payload = [])
    {
        return new AuthBrokerResponse($outcome, $payload);
    }
}