<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager as ManagerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\Fingerprinter;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Session\Session;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UAParser\Parser;

class AuthManager implements ManagerContract
{
    protected $config;

    protected $fingerprinter;

    protected $session;

    protected $encrypter;

    public function __construct(Encrypter $encrypter, 
                                Session $session, 
                                Fingerprinter $fingerprinter, 
                                array $config = []
    ) {
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

        $lifetime = $this->config['lifetimes']['authorization'] ?: 2628000;

        $response->headers->setCookie(
            (new CookieJar)->make(
                '_la_dat', 
                $enrypted, 
                $lifetime
            )
        );

        return $response;
    }

    public function getDeviceTokenCookie(Request $request)
    {
        if ($token = $request->cookies->get('_la_dat')) {
            return $this->encrypter->decrypt($token);
        }
    }

    public function setClientFingerprint(Request $request, $fingerprint)
    {
        $request->session()->put('client_fingerprint', $fingerprint);
    }

    public function forgetClientFingerprint(Request $request)
    {
        $request->session()->forget('client_fingerprint');
    }

    public function isAuthorized(Request $request)
    {
        $token = $this->getDeviceTokenCookie($request);

        $fingerprint = $this->fingerprint($request);

        if ($authorization = $request->user()->devices()->find($token)) {
            $algorithm = $this->config['fingerprints']['algorithm'];

            $fingerprintMatch = (
                hash($algorithm, $fingerprint) === $authorization->fingerprint
            );

            return $authorization->verified_at && $fingerprintMatch;
        }

        return false;
    }

    public function fingerprint(Request $request)
    {
        return $this->fingerprinter->fingerprint(
            $request
        );
    }

    public function resolveBrowserNameFromRequest(Request $request)
    {
        $parser = Parser::create();
        
        $result = $parser->parse($request->server->get('HTTP_USER_AGENT'));

        return $result->ua->family . ' (' . $result->os->family . ')';
    }

    public function resolveAddressFromRequest(Request $request)
    {
        if (!empty($request->server->get('HTTP_CLIENT_IP'))) {
            return $request->server->get('HTTP_CLIENT_IP');
        }

        if (!empty($request->server->get('HTTP_X_FORWARDED_FOR'))) {
            return $request->server->get('HTTP_X_FORWARDED_FOR');
        }

        return $request->server->get('REMOTE_ADDR');
    }
}