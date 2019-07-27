<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\DeviceAuthorizations;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

trait DeviceAuthorizations 
{
    public function devices(): HasMany
    {
        return $this->hasMany(
            config('device.authorization_model')
        );
    }

    public function scopePendingToken($query, $token)
    {
        $query
            ->where('token', '=', $token)
            ->whereNull('verified_at');
    }
}