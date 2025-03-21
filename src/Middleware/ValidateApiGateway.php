<?php

namespace Ms4Aeco\GatewayInternalAuth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class ValidateApiGateway
{
    /**
     * @var string Service-specific API key (required)
     */
    protected string $apiKey;

    /**
     * Create a new middleware instance.
     *
     * @param string|null $apiKey The unique API key for the service (required for route middleware, loaded from config for global middleware)
     */
    public function __construct(?string $apiKey = null)
    {
        // When used as global middleware, get the API key from config
        if ($apiKey === null) {
            $configApiKey = config('gateway_internal_auth.api_key.value');

            if (empty($configApiKey)) {
                throw new RuntimeException('API key must be provided or configured in gateway_internal_auth.api_key.value');
            }

            $this->apiKey = $configApiKey;
        } else {
            // When used as route middleware with explicit key
            $this->apiKey = $apiKey;
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware/handler in the pipeline
     * @return Response The HTTP response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $loggingEnabled = config('gateway_internal_auth.logging.enabled');

        // Only log request if it's not going to fail validation
        if ($loggingEnabled && $this->validateApiKey($request) && $this->validateGatewaySecret($request)) {
            $this->logRequest($request);
        }

        if (!$this->validateApiKey($request) || !$this->validateGatewaySecret($request)) {
            return $this->unauthorizedResponse($request);
        }

        return $next($request);
    }

    /**
     * Validate the API key in the request headers.
     *
     * @param Request $request The incoming HTTP request
     * @return bool True if valid or validation is disabled, false otherwise
     */
    protected function validateApiKey(Request $request): bool
    {
        if (!config('gateway_internal_auth.api_key.enabled')) {
            return true;
        }

        $header = config('gateway_internal_auth.api_key.header');
        $actualValue = $request->header($header);

        // Each service defines its own API key in its local environment or config
        $expectedValue = config('gateway_internal_auth.api_key.value');

        if (empty($expectedValue)) {
            Log::error('API key not configured.');
            return false;
        }

        return $actualValue === $expectedValue;
    }

    /**
     * Validate the gateway secret in the request headers.
     *
     * @param Request $request The incoming HTTP request
     * @return bool True if valid or validation is disabled, false otherwise
     */
    protected function validateGatewaySecret(Request $request): bool
    {
        if (!config('gateway_internal_auth.gateway_secret.enabled')) {
            return true;
        }

        $header = config('gateway_internal_auth.gateway_secret.header');
        $expectedValue = config('gateway_internal_auth.gateway_secret.value');

        $actualValue = $request->header($header);

        return $actualValue === $expectedValue;
    }

    /**
     * Generate an unauthorized response when validation fails.
     *
     * @param Request $request The incoming HTTP request
     * @return Response The 403 Forbidden response with appropriate error details
     */
    protected function unauthorizedResponse(Request $request): Response
    {
        $response = ['error' => 'Unauthorized access'];

        if (config('gateway_internal_auth.debug')) {
            $response['debug'] = [
                'api_key' => [
                    'header' => config('gateway_internal_auth.api_key.header'),
                    'received' => $request->header(config('gateway_internal_auth.api_key.header')),
                    'expected' => $this->apiKey,
                    'matches' => $request->header(config('gateway_internal_auth.api_key.header')) === $this->apiKey,
                ],
                'gateway_secret' => [
                    'header' => config('gateway_internal_auth.gateway_secret.header'),
                    'received' => $request->header(config('gateway_internal_auth.gateway_secret.header')),
                    'expected' => config('gateway_internal_auth.gateway_secret.value'),
                    'matches' => $request->header(config('gateway_internal_auth.gateway_secret.header')) === config('gateway_internal_auth.gateway_secret.value'),
                ],
            ];
        }

        if (config('gateway_internal_auth.logging.enabled')) {
            $this->logUnauthorized($request);
        }

        return response()->json($response, 403);
    }

    /**
     * Log successful API gateway requests.
     *
     * @param Request $request The incoming HTTP request
     * @return void
     */
    protected function logRequest(Request $request): void
    {
        $logChannel = config('gateway_internal_auth.logging.channel');
        $logLevel = config('gateway_internal_auth.logging.level', 'debug');

        $message = 'API Gateway Request: ' . $request->fullUrl();
        $context = [
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
        ];

        Log::channel($logChannel)->$logLevel($message, $context);
    }

    /**
     * Log unauthorized access attempts.
     *
     * @param Request $request The incoming HTTP request
     * @return void
     */
    protected function logUnauthorized(Request $request): void
    {
        $logChannel = config('gateway_internal_auth.logging.channel');
        $logLevel = config('gateway_internal_auth.logging.level', 'warning');

        $message = 'API Gateway Unauthorized Access: ' . $request->fullUrl();
        $context = [
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'api_key_value' => $request->header(config('gateway_internal_auth.api_key.header')),
            'gateway_secret_value' => $request->header(config('gateway_internal_auth.gateway_secret.header')),
        ];

        Log::channel($logChannel)->$logLevel($message, $context);
    }
}
