<?php

namespace BoxedCode\Laravel\Auth\Device;

use BoxedCode\Laravel\Auth\Device\Contracts\AuthBroker as BrokerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\AuthManager as ManagerContract;
use BoxedCode\Laravel\Auth\Device\Contracts\FingerprintManager as FingerprintsContract;
use BoxedCode\Laravel\Auth\Device\Fingerprints\FingerprintManager;
use Illuminate\Contracts\Hashing\Hasher;
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

        $this->registerAuthBroker();

        $this->registerAuthManager();
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

            $hasher = $app->make(Hasher::class);

            return (new AuthBroker($hasher, $config))
                ->setEventDispatcher($app['events']);
        });

        $this->app->alias(BrokerContract::class, 'auth.device.broker');
    }

    protected function registerAuthManager()
    {
        $this->app->bind(FingerprintsContract::class, function() {
            return new FingerprintManager($this->app);
        });

        $this->app->singleton(ManagerContract::class, function($app) {
            $config = config('device', []);

            $hasher = $app->make(Hasher::class);

            return new AuthManager(
                $hasher,
                $this->app['encrypter'],
                $this->app->make(Session::class),
                $this->app->make(FingerprintsContract::class),
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
        $this->publishes(
            [$this->packagePath('migrations') => database_path('migrations')], 
            'migrations'
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