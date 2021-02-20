<?php

namespace BoxedCode\Laravel\Auth\Device;

use Illuminate\Database\Eloquent\Relations\HasMany;

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
