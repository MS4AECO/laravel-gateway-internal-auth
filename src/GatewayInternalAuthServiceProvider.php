<?php

namespace Ms4Aeco\GatewayInternalAuth;

use Illuminate\Support\ServiceProvider;
use Ms4Aeco\GatewayInternalAuth\Middleware\ValidateApiGateway;
use Ms4Aeco\GatewayInternalAuth\Commands\PublishConfigCommand;

class GatewayInternalAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__ . '/config/gateway_internal_auth.php' => config_path('gateway_internal_auth.php'),
        ], 'config');
        $this->app['router']->aliasMiddleware('api.gateway', ValidateApiGateway::class);

        $this->registerCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/gateway_internal_auth.php',
            'gateway_internal_auth'
        );
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishConfigCommand::class,
            ]);
        }
    }
}
