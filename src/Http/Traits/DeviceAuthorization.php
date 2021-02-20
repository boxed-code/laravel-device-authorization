<?php

namespace BoxedCode\Laravel\Auth\Device\Http\Traits;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker;
use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization as Authorization;
use BoxedCode\Laravel\Auth\Device\Exceptions\DeviceAuthorizationLogicException;
use Illuminate\Http\Request;

trait DeviceAuthorization
{
    /**
     * Send a challenge to the user with a verification link.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function challenge(Request $request)
    {
        if (!$this->manager()->requiresAuthorization()) {
            return $this->routeResponse(AuthManager::NO_AUTH_REQUEST);
        }

        $fingerprint = $this->manager()->fingerprint($request);

        $browser = $this->manager()->resolveBrowserFromRequest($request);

        $ip = $this->manager()->resolveAddressFromRequest($request);

        $response = $this->broker()->challenge(
            $request->user(), $fingerprint, $browser, $ip
        );

        return $this->routeResponse($response);
    }

    /**
     * The user has been challenged.
     * 
     * @param  BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization $authorization
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function challenged(Authorization $authorization)
    {
        //
    }

    /**
     * Show the user challenged view.
     * 
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function showChallenged(Request $request)
    {
        return view('device_auth::challenged');
    }

    /**
     * Verify the challenge and authorize the user.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  string  $token  
     * @return \Symfony\Component\HttpFoundation\Response          
     */
    public function verifyAndAuthorize(Request $request, $token)
    {
        $fingerprint = $this->manager()->fingerprint($request);

        $response = $this->broker()->verifyAndAuthorize(
            $request->user(), $fingerprint, $token
        );

        return $this->routeResponse($response);
    }

    /**
     * The device has been authorized.
     * 
     * @param  BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization $authorization
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function authorized(Authorization $authorization)
    {
        //
    }

    /**
     * Send a response redirecting the user to the error view.
     * 
     * @param  string $message  
     * @param  \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendErrorResponse($message, $response)
    {
        return redirect()->route('device.error')
            ->withErrors([
                $message
            ]);
    }

    /**
     * Show the error view.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function showError(Request $request)
    {
        return view('device_auth::error');
    }

    /**
     * Route a broker response.
     * 
     * @param  \BoxedCode\Laravel\Auth\Device\AuthBrokerResponse $response
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws  \BoxedCode\Laravel\Auth\Device\Exceptions\DeviceAuthorizationLogicException
     */
    protected function routeResponse($response)
    {
        switch ($response)
        {
            // The user has been sent a challenge with a verification link.
            case AuthBroker::USER_CHALLENGED:
                return $this->challenged($response->authorization) ?? 
                    redirect()->route('device.challenged');

            // The device has been authorized.
            case AuthBroker::DEVICE_AUTHORIZED:
                $httpResponse = $this->authorized($response->authorization) ?? 
                        redirect()->to('/');

                return $this->manager()->setDeviceTokenCookie(
                    $httpResponse, $response->authorization->uuid
                );

            // The user cannot authorize devices.
            case AuthBroker::USER_CANNOT_AUTHORIZE_DEVICES:
                return $this->sendErrorResponse(
                    'The user cannot verify devices.',
                    $response
                );

            // The user presented an invalid verification token.
            case AuthBroker::INVALID_TOKEN:
                return $this->sendErrorResponse(
                    'The token supplied was incorrect.',
                    $response
                );

            // There was now authorization request in progress.
            case AuthManager::NO_AUTH_REQUEST:
                return redirect()->to('/');

            // The fingerprint does not match that of the requested authorization.
            case AuthBroker::INVALID_FINGERPRINT:
                return $this->sendErrorResponse(
                    'This device or browser does not match that of the authorization request.',
                    $response
                );

            // The authorization has expired.
            case AuthBroker::EXPIRED_REQUEST:
                return $this->sendErrorResponse(
                    'This authorization request has expired.',
                    $response
                );

        }

        throw new DeviceAuthorizationLogicException;
    }

    /**
     * Get the manager instance.
     * 
     * @return \BoxedCode\Laravel\Auth\Device\Contracts\AuthManager
     */
    public function manager()
    {
        return app('auth.device');
    }

    /**
     * Get the broker instance.
     * 
     * @return \BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker
     */
    public function broker()
    {
        return app('auth.device.broker');
    }
}