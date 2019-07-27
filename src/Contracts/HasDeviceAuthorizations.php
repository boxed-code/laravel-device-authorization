<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasDeviceAuthorizations
{
    public function canAuthorizeDevice(): bool;
    public function devices(): HasMany;
}