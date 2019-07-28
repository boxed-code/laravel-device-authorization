<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

interface AuthManager
{
    const NO_AUTH_REQUEST = 'no_auth_request';

    public function requestAuthorization();
    
    public function requiresAuthorization();
    
    public function revokeAuthorizationRequest();
    
    public function setDeviceTokenCookie(Response $response, $token);
    
    public function getDeviceTokenCookie(Request $request);
    
    public function setClientFingerprint(Request $request, $fingerprint);
    
    public function forgetClientFingerprint(Request $request);
    
    public function isAuthorized(Request $request);
    
    public function fingerprint(Request $request);
    
    public function resolveBrowserNameFromRequest(Request $request);
    
    public function resolveAddressFromRequest(Request $request);
    
}