<?php

namespace Ms4Aeco\GatewayInternalAuth\Tests;

use Orchestra\Testbench\TestCase;
use Ms4Aeco\GatewayInternalAuth\GatewayInternalAuthServiceProvider;
use Illuminate\Support\Facades\Log;
use Mockery;

class ValidateApiGatewayTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [GatewayInternalAuthServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default testing environment variables
        $app['config']->set('gateway_internal_auth.api_key.enabled', true);
        $app['config']->set('gateway_internal_auth.api_key.header', 'X-API-Key');
        $app['config']->set('gateway_internal_auth.api_key.value', 'test-api-key'); // Set this for constructor

        $app['config']->set('gateway_internal_auth.gateway_secret.enabled', true);
        $app['config']->set('gateway_internal_auth.gateway_secret.header', 'X-Gateway-Secret');
        $app['config']->set('gateway_internal_auth.gateway_secret.value', 'test-secret');

        $app['config']->set('gateway_internal_auth.debug', false);
        $app['config']->set('gateway_internal_auth.logging.enabled', false);
        $app['config']->set('gateway_internal_auth.logging.channel', 'stack');
        $app['config']->set('gateway_internal_auth.logging.level', 'debug');

        // Make sure Laravel knows about the log channels
        $app['config']->set('logging.channels.stack', [
            'driver' => 'stack',
            'channels' => ['single'],
        ]);

        $app['config']->set('logging.channels.single', [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ]);

        // Set up a test route
        $app['router']->middleware('api.gateway')->get('/api/test', function () {
            return response()->json(['status' => 'success']);
        });
    }

    public function testAccessIsAllowedWithValidCredentials()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testAccessIsDeniedWithInvalidApiKey()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'invalid-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
    }

    public function testAccessIsDeniedWithInvalidSecret()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            'X-Gateway-Secret' => 'invalid-secret',
        ])->get('/api/test');

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
    }

    public function testDebugInfoIsIncludedWhenEnabled()
    {
        $this->app['config']->set('gateway_internal_auth.debug', true);

        $response = $this->withHeaders([
            'X-API-Key' => 'invalid-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'error',
            'debug' => [
                'api_key',
                'gateway_secret'
            ]
        ]);
    }

    public function testDebugInfoIsNotIncludedWhenDisabled()
    {
        $this->app['config']->set('gateway_internal_auth.debug', false);

        $response = $this->withHeaders([
            'X-API-Key' => 'invalid-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
        $this->assertArrayNotHasKey('debug', $response->json());
    }

    public function testAccessIsDeniedWhenHeadersAreMissing()
    {
        $response = $this->get('/api/test');

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
    }

    public function testAccessIsAllowedWhenApiKeyValidationIsDisabled()
    {
        $this->app['config']->set('gateway_internal_auth.api_key.enabled', false);

        $response = $this->withHeaders([
            // No X-API-Key header
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testAccessIsAllowedWhenGatewaySecretValidationIsDisabled()
    {
        $this->app['config']->set('gateway_internal_auth.gateway_secret.enabled', false);

        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            // No X-Gateway-Secret header
        ])->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testCustomHeaderNames()
    {
        $this->app['config']->set('gateway_internal_auth.api_key.header', 'Custom-API-Key');
        $this->app['config']->set('gateway_internal_auth.gateway_secret.header', 'Custom-Gateway-Secret');

        $response = $this->withHeaders([
            'Custom-API-Key' => 'test-api-key',
            'Custom-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testLoggingEnabledForRequests()
    {
        // Setup the logging configuration in the package config.
        $this->app['config']->set('gateway_internal_auth.logging.enabled', true);
        $this->app['config']->set('gateway_internal_auth.logging.level', 'debug');
        $this->app['config']->set('gateway_internal_auth.logging.channel', 'stack'); // <-- This tells the middleware which log channel to use.

        // IMPORTANT: Set up (or mock) expectations on the Log facade BEFORE making the request!
        // Otherwise, the middleware’s log request call will occur before the mock is in place.
        Log::shouldReceive('channel')
            ->with('stack') // Expect that the middleware calls Log::channel('stack')
            ->andReturnSelf() // Return self so that chained calls (like ->debug(...)) work on the same mock.
            ->once();
        Log::shouldReceive('debug')->once(); // Expect the debug method to be called once.

        // Make sure Laravel's logging channels are configured in the test environment.
        $this->app['config']->set('logging.channels.stack', [
            'driver' => 'stack',
            'channels' => ['single'], // Log channel "stack" uses "single" underneath.
        ]);
        $this->app['config']->set('logging.channels.single', [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug', // Log level set to debug.
        ]);

        // Now, perform the request. The middleware will call Log::channel('stack')->debug(...)
        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test'); // <-- The request triggers the middleware and hence the log call.

        $response->assertStatus(200); // Expect a 200 status code (success).
    }


    public function testLoggingEnabledForUnauthorizedAccess()
    {
        // Configure logging settings to match our expectations
        $this->app['config']->set('gateway_internal_auth.logging.enabled', true);
        $this->app['config']->set('gateway_internal_auth.logging.level', 'warning'); // Explicitly match the default in the middleware
        $this->app['config']->set('gateway_internal_auth.logging.channel', 'stack');

        // Configure Mockery expectations BEFORE request is issued
        Log::shouldReceive('channel')
            ->with('stack')
            ->andReturnSelf()
            ->once(); // More precise expectation

        Log::shouldReceive('warning') // This must match $logLevel in the middleware method
            ->once();

        // Ensure logging channels configuration
        $this->app['config']->set('logging.channels.stack', [
            'driver' => 'stack',
            'channels' => ['single'],
        ]);

        $this->app['config']->set('logging.channels.single', [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ]);

        // Trigger the unauthorized access scenario
        $response = $this->withHeaders([
            'X-API-Key' => 'invalid-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        // Verify the response
        $response->assertStatus(403);
    }

    public function testFallbackToEnvVariableForServiceApiKey()
    {
        // Remove the config value to test fallback to env
        $this->app['config']->set('services.api_key', null);

        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testApiKeyConstructorInjection()
    {
        // Create a new route with an explicit API key passed to the middleware
        $this->app['router']->middleware('api.gateway:custom-injected-key')->get('/api/custom-key-test', function () {
            return response()->json(['status' => 'success']);
        });

        $this->app['config']->set('gateway_internal_auth.api_key.value', 'custom-injected-key');

        $response = $this->withHeaders([
            'X-API-Key' => 'custom-injected-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/custom-key-test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testPublishCommandFunctionality()
    {
        // Test the command execution
        $this->artisan('gateway-auth:publish')
            ->expectsOutput('Gateway authentication configuration published successfully!')
            ->assertExitCode(0);

        // Verify the config file exists in the expected location after publishing
        $configPath = config_path('gateway_internal_auth.php');
        $this->assertTrue(file_exists($configPath));

        // Ensure the content is correct (optional, but thorough)
        $configContent = file_get_contents($configPath);
        $this->assertStringContainsString('api_key', $configContent);
        $this->assertStringContainsString('gateway_secret', $configContent);
    }

    public function testAccessIsAllowedWhenBothAuthMethodsAreDisabled()
    {
        // Disable both authentication methods
        $this->app['config']->set('gateway_internal_auth.api_key.enabled', false);
        $this->app['config']->set('gateway_internal_auth.gateway_secret.enabled', false);

        // Request should succeed without any authentication headers
        $response = $this->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testDifferentLogLevelsAreUsedCorrectly()
    {
        // Configure custom log level
        $this->app['config']->set('gateway_internal_auth.logging.enabled', true);
        $this->app['config']->set('gateway_internal_auth.logging.level', 'info');
        $this->app['config']->set('gateway_internal_auth.logging.channel', 'stack');

        Log::shouldReceive('channel')
            ->with('stack')
            ->andReturnSelf()
            ->once();

        Log::shouldReceive('info')
            ->once();

        // Make the request
        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
    }

    public function testHeadersAreCaseInsensitive()
    {
        // Use mixed case in header names
        $response = $this->withHeaders([
            'x-Api-kEy' => 'test-api-key',
            'X-gateway-SECRET' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testLogDisabledConfiguration()
    {
        // Explicitly disable logging
        $this->app['config']->set('gateway_internal_auth.logging.enabled', false);

        // No log expectations - if any logging happens, the test will fail
        // since we're not setting up any expectations on the Log facade

        $response = $this->withHeaders([
            'X-API-Key' => 'test-api-key',
            'X-Gateway-Secret' => 'test-secret',
        ])->get('/api/test');

        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
