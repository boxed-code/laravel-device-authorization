<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager as ManagerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\FingerprintManager as Fingerprint;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthManager implements ManagerContract
{
    protected $config;

    protected $fingerprinter;

    protected $session;

    protected $encrypter;

    protected $hasher;

    public function __construct(Hasher $hasher, 
                                Encrypter $encrypter, 
                                Session $session, 
                                Fingerprint $fingerprinter, 
                                array $config = []
    ) {
        $this->hasher = $hasher;

        $this->encrypter = $encrypter;

        $this->session = $session;

        $this->fingerprinter = $fingerprinter;

        $this->config = $config;
    }

    public function requestAuthorization()
    {
        $this->session->put('_lda_na', true);

        return redirect()->route('device.challenge');
    }   

    public function requiresAuthorization()
    {
        return $this->session->has('_lda_na');
    }

    public function revokeAuthorizationRequest()
    {
        $this->session->forget('_lda_na');
    }

    public function setDeviceTokenCookie(Response $response, $token)
    {
        $enrypted = $this->encrypter->encrypt($token);

        $response->headers->setCookie(
            (new CookieJar)->make('_la_dat', $enrypted, 60 * 24 * 365)
        );

        return $response;
    }

    public function getDeviceTokenCookie(Request $request)
    {
        if ($token = $request->cookies->get('_la_dat')) {
            return $this->encrypter->decrypt($token);
        }
    }

    public function isAuthorized(Request $request)
    {
        $token = $this->getDeviceTokenCookie($request);

        $fingerprint = $this->fingerprint($request);

        if ($authorization = $request->user()->devices()->find($token)) {
            return $this->hasher->check(
                $fingerprint, 
                $authorization->fingerprint
            );
        }

        return false;
    }

    public function fingerprint(Request $request)
    {
        return $this->fingerprinter->fingerprint(
            $request
        );
    }
}