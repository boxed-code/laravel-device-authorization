<?php

namespace BoxedCode\Laravel\Auth\Device\Listeners;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogoutListener
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
     * @param \Illuminate\Http\Request                             $request
     */
    public function __construct(AuthManager $manager, Request $request)
    {
        $this->manager = $manager;

        $this->request = $request;
    }

    /**
     * Handle the logout event.
     *
     * @param \Illuminate\Auth\Events\Logout $event
     *
     * @return void
     */
    public function handle(Logout $event)
    {
        $this->manager->forgetClientFingerprint(
            $this->request
        );
    }
}
