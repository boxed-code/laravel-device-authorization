<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\AuthBrokerResponse;
use BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker as BrokerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthBroker implements BrokerContract
{
    protected $config;

    protected $dispatcher;

    protected $hasher;

    public function __construct(Hasher $hasher, array $config = [])
    {
        $this->hasher = $hasher;

        $this->config = $config;
    }

    public function challenge(HasDeviceAuthorizations $user, $fingerprint, $browser, $ip)
    {
        // Flush all other pending authorizations for this user.
        $user->devices()->pending()->delete();

        $fingerprintHash = $this->hasher->make($fingerprint);

        // Create the authorizations
        $authorization = $user->devices()->create([
            'uuid' => Str::uuid(),
            'fingerprint' => $fingerprintHash,
            'browser' => $browser,
            'ip' => $ip,
            'verify_token' => $token = $this->newVerifyToken(),
        ]);

        // Send the request and verification token        
        $user->notify(new $this->config['notification']($token, $browser, $ip));

        $this->event(new Events\Challenged($authorization));

        return $this->respond(static::USER_CHALLENGED, [
            'authorization' => $authorization
        ]);
    }   

    public function verify(HasDeviceAuthorizations $user, $fingerprint, $token)
    {
        // Verify the token.
        if (!($authorization = $user->devices()->pending($token)->first())) {
            return $this->respond(static::INVALID_TOKEN);
        }

        // Verify the fingerprints match.
        if (!$this->hasher->check($fingerprint, $authorization->fingerprint)) {
            return $this->respond(static::INVALID_FINGERPRINT);
        }

        // Mark the authorization as verified
        $authorization->fill(['verified_at' => now()])->save();

        $this->event(new Events\Verified($authorization));

        return $this->respond(static::DEVICE_VERIFIED, [
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

    protected function newVerifyToken()
    {
        return Str::random(40);
    }

    protected function respond($outcome, array $payload = [])
    {
        return new AuthBrokerResponse($outcome, $payload);
    }

}