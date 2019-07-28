<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\DeviceAuthorizations;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

trait DeviceAuthorizations 
{
    /**
     * The users device authorization relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deviceAuthorizations(): HasMany
    {
        return $this->hasMany(
            config('device.authorization_model')
        );
    }
}