<?php

namespace Tests\Integration;

use BoxedCode\Laravel\Auth\Device\Notifications\AuthorizationRequest;
use Illuminate\Encryption\Encrypter;

class EnforcingMiddlewareTestCase extends TestCase
{
    protected $da;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->da = app('auth.device.broker');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Set the application encryption key.
        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));
    }

    public function testDefaultFlow()
    {
        \Notification::fake();

        // Trigger auth request as an unverified user.
        $response = $this->actingAs($this->testUser)->get('/');
        $response->assertRedirect('http://localhost/auth/device/challenge');

        // Visit the challenge page which will send us the email.
        $response = $this->actingAs($this->testUser)->get('/auth/device/challenge');
        $response->assertRedirect('http://localhost/auth/device/challenged');

        // Visit the 'challenged' page which instructs the user to check their mail.
        $response = $this->actingAs($this->testUser)->get('/auth/device/challenged');
        $response->assertSee('We haven\'t seen you using this device before');

        $latestAuthorization = $this->testUser->deviceAuthorizations()->latest()->first();

        // Verify the notification was sent.
        \Notification::assertSentTo($this->testUser, AuthorizationRequest::class, function ($mail) use ($latestAuthorization) {
            return $mail->verifyToken === $latestAuthorization->verify_token &&
                $mail->browser === $latestAuthorization->browser &&
                $mail->ip === $latestAuthorization->ip;
        });

        // 'Click' the link in the notification e-mail.
        $response = $this->actingAs($this->testUser)
            ->withoutMiddleware([\Illuminate\Cookie\Middleware\EncryptCookies::class])
            ->get('/auth/device/verify/'.$latestAuthorization->verify_token);
        $response->assertRedirect('/');

        $authCookie = collect($response->headers->getCookies())
            ->filter(function ($cookie) {
                return $cookie->getName() === '_la_dat';
            })->first();

        // Check we can see the homepage.
        $response = $this->withUnencryptedCookie($authCookie->getName(), $authCookie->getValue())
            ->actingAs($this->testUser)->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('Hello Test User!');
    }
}
