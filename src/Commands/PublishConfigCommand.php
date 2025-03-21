<?php

namespace Ms4Aeco\GatewayInternalAuth\Commands;

use Illuminate\Console\Command;

class PublishConfigCommand extends Command
{
    protected $signature = 'gateway-auth:publish';
    protected $description = 'Publish gateway authentication configuration';

    public function handle()
    {
        $this->call('vendor:publish', [
            '--provider' => 'Ms4Aeco\GatewayInternalAuth\GatewayInternalAuthServiceProvider',
            '--tag' => 'config'
        ]);

        $this->info('Gateway authentication configuration published successfully!');
    }
}
