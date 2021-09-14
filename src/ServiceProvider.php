<?php

namespace DevAdamlar\LaravelId3global;

use ID3Global\Gateway\GlobalAuthenticationGateway;
use ID3Global\Service\GlobalAuthenticationService;
use Illuminate\Foundation\Application;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $configPath = __DIR__.'/../config/id3global.php';
        $this->mergeConfigFrom($configPath, 'id3global');

        $this->app->singleton(GlobalAuthenticationService::class, function (Application $app) {
            $pilot = config('id3global.use_pilot') ?: !$app->environment('Production');

            $gateway = new GlobalAuthenticationGateway(config('id3global.username'), config('id3global.password'), [], $pilot);

            return new GlobalAuthenticationService($gateway);
        });
    }

    public function boot()
    {
        $configPath = __DIR__.'/../config/id3global.php';
        $this->publishes([$configPath => config_path()], 'config');
    }
}
