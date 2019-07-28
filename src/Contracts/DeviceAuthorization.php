<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface DeviceAuthorization
{
    public function user(): BelongsTo;

    public function scopeFingerprint($query, $fingerprint);

    public function scopePending($query, $token = null);
}