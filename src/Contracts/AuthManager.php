<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

interface AuthManager
{
    /**
     * Constant requests there is not authorization request in progress.
     */
    const NO_AUTH_REQUEST = 'no_auth_request';
    
    /**
     * Determine whether a request should be authorized or denied.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return boolean
     */
    public function isAuthorized(Request $request);

    /**
     * Request device authorization and redirect.
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestAuthorization();

    /**
     * Determine whether a device authorization is progress.
     * 
     * @return bool
     */
    public function requiresAuthorization();
    
    /**
     * Revoke a device authorization request.
     * 
     * @return void
     */
    public function revokeAuthorizationRequest();
    
    /**
     * Set the device token cookie.
     *
     * The cookie is used to store the authorizations UUID which is checked 
     * against the database, a long with the fingerprint to ensure the 
     * device is authorized.
     * 
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setDeviceTokenCookie(Response $response, $token);
    
    /**
     * Gets the device token cookie.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function getDeviceTokenCookie(Request $request);
    
    /**
     * Set the client fingerprint.
     *
     * A client fingerprint can be set to increase entropy of the fingerprint. 
     * This can be used with a client side fingerprinting library, such as 
     * fingerprint.js, which will enable a more unique fingerprint than relying 
     * on server-side variables only.
     *
     * You can automatically populate this value by sending a '_fingerprint' field a 
     * long with the usual form fields.
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $fingerprint
     */
    public function setClientFingerprint(Request $request, $fingerprint);
    
    /**
     * Forget the client fingerprint.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function forgetClientFingerprint(Request $request);
    
    /**
     * Create a fingerprint for a request instance.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function fingerprint(Request $request);

    /**
     * Resolve a browser name from a request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function resolveBrowserNameFromRequest(Request $request);
    
    /**
     * Resolve an IP address from a request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function resolveAddressFromRequest(Request $request);
}