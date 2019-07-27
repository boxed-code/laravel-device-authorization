<?php

namespace BoxedCode\Laravel\Auth\Device\Listeners;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LoginListener
{
    protected $manager;

    protected $request;

    public function __construct(AuthManager $manager, Request $request)
    {
        $this->manager = $manager;

        $this->request = $request;
    }

    public function handle(Login $event)
    {
        if ($this->request->has('_fingerprint')) {
            $this->manager->setClientFingerprint(
                $this->request, $this->request->input('_fingerprint')
            );
        }
    }
}
