<?php

namespace BoxedCode\Laravel\Auth\Device\Listeners;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogoutListener
{
    protected $manager;

    protected $request;

    public function __construct(AuthManager $manager, Request $request)
    {
        $this->manager = $manager;

        $this->request = $request;
    }

    public function handle(Logout $event)
    {
        $this->manager->forgetClientFingerprint(
            $this->request
        );
    }
}
