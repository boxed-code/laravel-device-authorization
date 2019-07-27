<?php

namespace BoxedCode\Laravel\Auth\Device\Http\Traits;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker;
use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager;
use BoxedCode\Laravel\Auth\Device\Contracts\DeviceAuthorization as Authorization;
use BoxedCode\Laravel\Auth\Device\Contracts\HasDeviceAuthorizations as HasAuthorizations;
use BoxedCode\Laravel\Auth\Device\Exceptions\DeviceAuthorizationLogicException;
use Illuminate\Http\Request;
use UAParser\Parser;

trait DeviceAuthorization
{
    public function challenge(Request $request)
    {
        if (!$this->manager()->requiresAuthorization()) {
            return $this->routeResponse(AuthManager::NO_AUTH_REQUEST);
        }

        $fingerprint = $this->manager()->fingerprint($request);

        $browser = $this->resolveBrowserNameFromRequest($request);

        $ip = $this->resolveAddressFromRequest($request);

        $response = $this->broker()->challenge(
            $request->user(), $fingerprint, $browser, $ip
        );

        return $this->routeResponse($response);
    }

    protected function challenged(Authorization $authorization)
    {
        //
    }

    public function showChallenged(Request $request)
    {
        return view('device_auth::challenged');
    }

    public function verify(Request $request, $token)
    {
        $fingerprint = $this->manager()->fingerprint($request);

        $response = $this->broker()->verify(
            $request->user(), $fingerprint, $token
        );

        return $this->routeResponse($response);
    }

    protected function verified(Authorization $authorization)
    {
        //
    }

    protected function sendErrorResponse($message)
    {
        return redirect()->route('device.error')
            ->withErrors([
                $message
            ]);
    }

    public function showError(Request $request)
    {
        return view('device_auth::error');
    }

    protected function routeResponse($response)
    {
        switch ($response)
        {
            case AuthBroker::USER_CHALLENGED:
                return $this->challenged($response->authorization) ?? 
                    redirect()->route('device.challenged');

            case AuthBroker::DEVICE_VERIFIED:
                $httpResponse = $this->verified($response->authorization) ?? 
                        redirect()->to('/');

                return $this->manager()->setDeviceTokenCookie(
                    $httpResponse, $response->authorization->uuid
                );

            case AuthBroker::INVALID_TOKEN:
                return $this->sendErrorResponse('The token supplied was incorrect.');

            case AuthManager::NO_AUTH_REQUEST:
                return redirect()->to('/');

            case AuthBroker::INVALID_FINGERPRINT:
                return $this->sendErrorResponse('This device or browser does not match that of the authorization request.');
        }

        throw new DeviceAuthorizationLogicException;
    }

    protected function resolveBrowserNameFromRequest(Request $request)
    {
        $parser = Parser::create();
        
        $result = $parser->parse($request->server->get('HTTP_USER_AGENT'));

        return $result->ua->family . ' (' . $result->os->family . ')';
    }

    protected function resolveAddressFromRequest(Request $request)
    {
        if (!empty($request->server->get('HTTP_CLIENT_IP'))) {
            return $request->server->get('HTTP_CLIENT_IP');
        }

        if (!empty($request->server->get('HTTP_X_FORWARDED_FOR'))) {
            return $request->server->get('HTTP_X_FORWARDED_FOR');
        }

        return $request->server->get('REMOTE_ADDR');
    }

    public function manager()
    {
        return app('auth.device');
    }

    public function broker()
    {
        return app('auth.device.broker');
    }
}