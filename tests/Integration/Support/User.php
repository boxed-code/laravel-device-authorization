<?php

namespace Tests\Integration\Support;

use Illuminate\Foundation\Auth\User as BaseUser;

class User extends BaseUser implements \BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations
{
    use \BoxedCode\Laravel\Auth\Device\DeviceAuthorizations,
        \Illuminate\Notifications\Notifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    public function canAuthorizeDevice(): bool
    {
        return true;
    }
}
