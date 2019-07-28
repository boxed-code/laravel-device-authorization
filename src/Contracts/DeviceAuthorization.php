<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface DeviceAuthorization
{
    public function user(): BelongsTo;

    public function scopePendingToken($query, $token);
}