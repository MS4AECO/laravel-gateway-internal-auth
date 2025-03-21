# Laravel API Gateway Internal Authentication

![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)
![License](https://img.shields.io/badge/License-MIT-blue.svg)

A Laravel package for validating internal service-to-service communication through an API Gateway. Protects your APIs with dual-layer authentication using custom headers validation.

## Overview

This package implements a security layer for microservices architectures where services communicate through an API gateway. It validates requests using:

1. **API Key** - A unique key for each service
2. **Gateway Secret** - A shared secret known only to the API gateway

## Installation

Install the package via Composer:

```bash
composer require ms4aeco/laravel-gateway-internal-auth
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Ms4Aeco\GatewayInternalAuth\GatewayInternalAuthServiceProvider" --tag="config"
```

This will create a `gateway_internal_auth.php` file in your config directory.

### Environment Variables

Add these variables to your `.env` file on the respective services implementing this package:

```
API_SERVICE_KEY_ENABLED=true
API_SERVICE_KEY_HEADER=X-API-Key
API_SERVICE_KEY=your-api-key-here

API_GATEWAY_SECRET_ENABLED=true
API_GATEWAY_SECRET_HEADER=X-Gateway-Secret
API_GATEWAY_SECRET=your-gateway-secret-here

API_GATEWAY_LOGGING_ENABLED=false
API_GATEWAY_LOGGING_LEVEL=debug
API_GATEWAY_LOGGING_CHANNEL=stack

API_GATEWAY_DEBUG=false
```

## Usage

### Route Middleware

Apply the middleware to specific routes or route groups:

```php
// Single route
Route::get('/api/data', 'DataController@index')
    ->middleware('api.gateway');

// Route group
Route::middleware(['api.gateway'])
    ->prefix('api')
    ->group(function () {
        // Protected routes
    });
```

#### With Custom API Key

You can pass a specific API key to the middleware:

```php
Route::get('/api/sensitive-data', 'DataController@sensitive')
    ->middleware('api.gateway:custom-api-key-for-sensitive-routes');
```

### Global Middleware

To protect all routes, add the middleware to your `app/Http/Kernel.php` file:

```php
protected $middleware = [
    // ...
    \Ms4Aeco\GatewayInternalAuth\Middleware\ValidateApiGateway::class,
];
```

When used globally, the middleware will load the API key from your configuration.

## Advanced Configuration

### Security Layers

Both security layers (API key and Gateway secret) can be enabled/disabled independently:

```php
// config/gateway_internal_auth.php
'api_key' => [
    'enabled' => env('API_SERVICE_KEY_ENABLED', true),
    // ...
],
'gateway_secret' => [
    'enabled' => env('API_GATEWAY_SECRET_ENABLED', true),
    // ...
],
```

### Debugging

For development environments, enable debug mode to see detailed validation information:

```php
'debug' => env('API_GATEWAY_DEBUG', false),
```

When enabled, unauthorized requests will return JSON with debugging details about what failed.

### Logging

Configure request logging for monitoring and troubleshooting:

```php
'logging' => [
    'enabled' => env('API_GATEWAY_LOGGING_ENABLED', false),
    'level' => env('API_GATEWAY_LOGGING_LEVEL', 'debug'),
    'channel' => env('API_GATEWAY_LOGGING_CHANNEL', 'stack'),
],
```

## Header Format

The middleware expects headers in this format:

```
X-API-Key: your-api-key
X-Gateway-Secret: your-gateway-secret
```

Header names are configurable in the settings.

## Testing

The package includes comprehensive tests. To run them:

```bash
composer test
```

## Security Considerations

- Use HTTPS for all API communications
- Store keys and secrets securely in environment variables
- Rotate secrets periodically
- Enable logging in production to detect unauthorized access attempts
- Consider using gateway-generated JWT for more advanced use cases

## License

This package is open-sourced software licensed under the MIT license.
