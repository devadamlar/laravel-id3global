<?php

namespace DevAdamlar\LaravelId3global;

use ID3Global\Gateway\GlobalAuthenticationGateway;
use ID3Global\Stubs\Gateway\GlobalAuthenticationGatewayFake;
use Illuminate\Foundation\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__ . '/../config/id3global.php';
        $this->mergeConfigFrom($configPath, 'id3global');

        $this->app->bind(GlobalAuthenticationGateway::class, function (Application $app) {
            if ($app->environment('testing')) {
                return new GlobalAuthenticationGatewayFake(config('username'), config('password'));
            }

            $pilot = !$app->environment('Production');

            return new GlobalAuthenticationGateway(config('username'), config('password'), [], $pilot);
        });
    }

    public function boot()
    {
        $configPath = __DIR__ . '/../config/id3global.php';
        $this->publishes([$configPath => config_path()], 'config');
    }

}