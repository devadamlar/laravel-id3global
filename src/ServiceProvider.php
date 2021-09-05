<?php

namespace DevAdamlar\LaravelId3global;

use ID3Global\Gateway\GlobalAuthenticationGateway;
use ID3Global\Service\GlobalAuthenticationService;
use ID3Global\Stubs\Gateway\GlobalAuthenticationGatewayFake;
use Illuminate\Foundation\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__.'/../config/id3global.php';
        $this->mergeConfigFrom($configPath, 'id3global');

        $this->app->bind(GlobalAuthenticationService::class, function (Application $app) {
            $pilot = config('use_pilot') || !$app->environment('Production');

            $gateway = new GlobalAuthenticationGateway(config('username'), config('password'), [], $pilot);

            if ($app->environment('testing')) {
                $gateway = new GlobalAuthenticationGatewayFake(config('username'), config('password'));
            }

            return new GlobalAuthenticationService($gateway);
        });
    }

    public function boot()
    {
        $configPath = __DIR__.'/../config/id3global.php';
        $this->publishes([$configPath => config_path()], 'config');
    }
}
