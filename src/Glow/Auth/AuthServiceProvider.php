<?php

namespace Glow\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {

        if ($this->app->runningInConsole()) {

            $this->publishes([
                                 __DIR__ . '/../../../config/glow.php' => config_path('glow.php'),
                             ], 'config');

            $this->publishes([
                                 __DIR__ . '/../../../database/migrations' => database_path('migrations'),
                             ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {

        $this->registerTokenRepository();
        $this->registerGuard();
    }

    /**
     * Register the token repository.
     *
     * @return void
     */
    protected function registerTokenRepository() {

        $this->app->singleton(TokenRepository::class, function ($app) {

            $connection = $app[ 'db' ]->connection();

            $config = $app[ 'config' ]->get('glow.auth.guard.token');

            $key = $app[ 'config' ][ 'app.key' ];

            return new TokenRepository($connection, $config[ 'table' ], $key, $config[ 'expire' ],
                                       $config[ 'multiple' ]);
        });
    }

    /**
     * Register the token guard.
     *
     * @return void
     */
    protected function registerGuard() {

        Auth::extend('token', function ($app, $name, array $config) {

            return new TokenGuard(
                Auth::createUserProvider($config[ 'provider' ]),
                $app[ 'request' ],
                $app->make(TokenRepository::class)
            );
        });
    }
}