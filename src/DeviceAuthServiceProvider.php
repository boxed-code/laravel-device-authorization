<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker as BrokerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager as ManagerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\Fingerprinter as FingerprinterContract;
use BoxedCode\Laravel\Auth\Device\Fingerprinter;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Session\Session;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class DeviceAuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/device.php'), 
            'device'
        );

        $this->registerFingerPrinter();

        $this->registerAuthBroker();

        $this->registerAuthManager();
    }

    /**
     * Register the fingerprint manager.
     * 
     * @return void
     */
    public function registerFingerPrinter()
    {
        $this->app->bind(FingerprinterContract::class, function() {
            $config = config('device.fingerprints', []);

            return new Fingerprinter($config);
        });
    }

    /**
     * Register the authentication broker instance.
     * 
     * @return void
     */
    protected function registerAuthBroker()
    {
        $this->app->bind(BrokerContract::class, function($app) {
            $config = config('device', []);

            return (new AuthBroker($config))
                ->setEventDispatcher($app['events']);
        });

        $this->app->alias(BrokerContract::class, 'auth.device.broker');
    }

    /**
     * Register the authentication manager.
     * 
     * @return void
     */
    protected function registerAuthManager()
    {
        $this->app->singleton(ManagerContract::class, function($app) {
            $config = config('device', []);

            return new AuthManager(
                $app->make(Encrypter::class),
                $app->make(Session::class),
                $app->make(FingerprinterContract::class),
                $config
            );
        });

        $this->app->alias(ManagerContract::class, 'auth.device');
    }

    /**
     * Application is booting.
     *
     * @return void
     */
    public function boot()
    {
        // Register the packages route macros.
        $this->registerRouteMacro();

        // Register the package views.
        $this->loadViewsFrom($this->packagePath('views'), 'device_auth');

        // Register the package configuration to publish.
        $this->publishes(
            [$this->packagePath('config/device.php') => config_path('device.php')], 
            'config'
        );

        // Register the migrations to publish.
        $this->loadMigrationsFrom($this->packagePath('migrations'));

        // Register the event listeners.
        $this->app['events']->listen(
            \Illuminate\Auth\Events\Logout::class, 
            \BoxedCode\Laravel\Auth\Device\Listeners\LogoutListener::class
        );

        $this->app['events']->listen(
            \Illuminate\Auth\Events\Login::class, 
            \BoxedCode\Laravel\Auth\Device\Listeners\LoginListener::class
        );
    }

    /**
     * Register the router macro.
     * 
     * @return void
     */
    protected function registerRouteMacro()
    {
        $registerRoutes = function() { 
            $this->loadRoutesFrom(
                $this->packagePath('src/Http/routes.php')
            ); 
        };

        Router::macro('deviceAuth', function() use ($registerRoutes) {
            $registerRoutes();
        });

        // Register the routes automatically if required.
        if ($this->app['config']->get('device.routing.register') === true) {
            $registerRoutes();
        }
    }

    /**
     * Loads a path relative to the package base directory.
     *
     * @param string $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf('%s/../%s', __DIR__, $path);
    }
}