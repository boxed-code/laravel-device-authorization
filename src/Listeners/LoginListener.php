<?php

namespace BoxedCode\Laravel\Auth\Device\Listeners;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LoginListener
{
    /**
     * The manager instanc.
     * 
     * @var \BoxedCode\Laravel\Auth\Device\Contracts\AuthManager
     */
    protected $manager;

    /**
     * The request instance.
     * 
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new listener instance.
     * 
     * @param \BoxedCode\Laravel\Auth\Device\Contracts\AuthManager $manager
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(AuthManager $manager, Request $request)
    {
        $this->manager = $manager;

        $this->request = $request;
    }

    /**
     * Handle the login event.
     * 
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        if ($this->request->has('_fingerprint')) {
            $this->manager->setClientFingerprint(
                $this->request, $this->request->input('_fingerprint')
            );
        }
    }
}
