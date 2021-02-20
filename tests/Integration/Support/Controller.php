<?php

namespace Tests\Integration\Support;

use BoxedCode\Laravel\Auth\Device\Http\Traits\DeviceAuthorization;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use DeviceAuthorization;
    use AuthenticatesUsers;

    public function home(Request $request)
    {
        return 'Hello '.$request->user()->name.'!';
    }
}
