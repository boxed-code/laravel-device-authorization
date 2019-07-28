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
    /**
     * The configuration.
     * 
     * @var array
     */
    protected $config;

    /**
     * The fingerprinter instance.
     * 
     * @var \BoxedCode\Laravel\Auth\Device\Contracts\Fingerprinter
     */
    protected $fingerprinter;

    /**
     * The session instance.
     * 
     * @var \Illuminate\Contracts\Session\Session
     */
    protected $session;

    /**
     * The encrypter instance.
     * 
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Create a new manager instance.
     * 
     * @param \Illuminate\Contracts\Encryption\Encrypter  $encrypter
     * @param \Illuminate\Contracts\Session\Session  $session
     * @param \BoxedCode\Laravel\Auth\Device\Contracts\Fingerprinter  $fingerprinter
     * @param array  $config 
     */
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

    /**
     * Determine whether a request should be authorized or denied.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return boolean
     */
    public function isAuthorized(Request $request)
    {
        $token = $this->getDeviceTokenCookie($request);

        $fingerprint = $this->fingerprint($request);

        if ($authorization = $request->user()->deviceAuthorizations()->find($token)) {
            $algorithm = $this->config['fingerprints']['algorithm'];

            $fingerprintMatch = (
                hash($algorithm, $fingerprint) === $authorization->fingerprint
            );

            return $authorization->verified_at && $fingerprintMatch;
        }

        return false;
    }

    /**
     * Request device authorization and redirect.
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestAuthorization()
    {
        $this->session->put('_lda_na', true);

        return redirect()->route('device.challenge');
    }   

    /**
     * Determine whether a device authorization is progress.
     * 
     * @return bool
     */
    public function requiresAuthorization()
    {
        return $this->session->has('_lda_na');
    }

    /**
     * Revoke a device authorization request.
     * 
     * @return void
     */
    public function revokeAuthorizationRequest()
    {
        $this->session->forget('_lda_na');
    }

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

    /**
     * Gets the device token cookie.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function getDeviceTokenCookie(Request $request)
    {
        if ($token = $request->cookies->get('_la_dat')) {
            return $this->encrypter->decrypt($token);
        }
    }

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
    public function setClientFingerprint(Request $request, $fingerprint)
    {
        $request->session()->put('client_fingerprint', $fingerprint);
    }

    /**
     * Forget the client fingerprint.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function forgetClientFingerprint(Request $request)
    {
        $request->session()->forget('client_fingerprint');
    }

    /**
     * Create a fingerprint for a request instance.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function fingerprint(Request $request)
    {
        return $this->fingerprinter->fingerprint(
            $request
        );
    }

    /**
     * Resolve a browser name from a request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function resolveBrowserNameFromRequest(Request $request)
    {
        $parser = Parser::create();
        
        $result = $parser->parse($request->server->get('HTTP_USER_AGENT'));

        return $result->ua->family . ' (' . $result->os->family . ')';
    }

    /**
     * Resolve an IP address from a request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
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