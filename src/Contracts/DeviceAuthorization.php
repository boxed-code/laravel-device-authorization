<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface DeviceAuthorization
{
    /**
     * Get the authorizations user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo;

    /**
     * Filter a model query by a fingerprint which has been verified.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $fingerprint
     *
     * @return void
     */
    public function scopeVerifiedFingerprint($query, $fingerprint);

    /**
     * Filter a model query for non-verified items and optionally for a given token.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $verify_token
     *
     * @return void
     */
    public function scopePending($query, $verify_token = null);
}
