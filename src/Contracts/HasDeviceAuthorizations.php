<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasDeviceAuthorizations
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $instance
     * @return void
     */
    public function notify($instance);

    /**
     * Determine whether the user can authorize devices.
     * 
     * @return bool
     */
    public function canAuthorizeDevice(): bool;

    /**
     * The users device authorizations relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deviceAuthorizations(): HasMany;
}