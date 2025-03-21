<?php

namespace Ms4Aeco\GatewayInternalAuth;

use Illuminate\Support\ServiceProvider;
use Ms4Aeco\GatewayInternalAuth\Middleware\ValidateApiGateway;

class GatewayInternalAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/gateway_internal_auth.php' => config_path('gateway_internal_auth.php'),
        ], 'config');

        $this->app['router']->aliasMiddleware('api.gateway', ValidateApiGateway::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/gateway_internal_auth.php',
            'gateway_internal_auth'
        );
    }
}
