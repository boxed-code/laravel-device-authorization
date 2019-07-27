<?php

namespace BoxedCode\Laravel\Auth\Device\Http\Middleware;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use Closure;

class RequireDeviceAuthorization
{
    /**
     * The session manager instance.
     * 
     * @var \BoxedCode\Laravel\Auth\Device\Contracts\AuthManager
     */
    protected $manager;

    /**
     * Create a new middleware instance.
     * 
     * @param AuthManager $manager
     */
    public function __construct(AuthManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * The paths that should be excluded from 
     * two factor authentication.
     *
     * @var array
     */
    protected $except = [
        '/auth/device*',
        '/login',
        '/logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $methods = null)
    {
        if ($this->shouldAuthenticate($request, $methods)) {

            if ($request->expectsJson()) {
                throw new AuthenticationException;
            }

            return $this->manager->requestAuthorization();
        }

        return $next($request);
    }

    /**
     * Ascertain whether we should redirect for authentication.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function shouldAuthenticate($request, $methods)
    {
        return !$this->inExceptArray($request) &&
            !$this->manager->isAuthorized($request);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
