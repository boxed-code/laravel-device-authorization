<?php

namespace BoxedCode\Laravel\Auth\Device\Models;

use BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization as Contract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAuthorization extends Model implements Contract
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'device_authorizations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'fingerprint',
        'browser',
        'ip',
        'user_id',
        'verify_token',
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['verify_token', 'fingerprint'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['verified_at'];

    /**
     * Get the user the model belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            $this->getModelForGuard(),
            'user_id'
        );
    }

    public function scopeFingerprint($query, $fingerprint)
    {
        $query->where('fingerprint', '=', $fingerprint);
    }

    public function scopePending($query, $token = null)
    {
        $query->whereNull('verified_at');

        if ($token) {
            $query->where('verify_token', '=', $token);
        }
    }

    /**
     * Get the model associate with an authentication guard.
     * 
     * @param  string|null $guard
     * @return string
     */
    protected function getModelForGuard($guard = null)
    {
        if (empty($guard)) {
            $guard = config('auth.defaults.guard');
        }

        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return;
                }

                return config("auth.providers.{$guard['provider']}.model");
            })->get($guard);
    }
}
