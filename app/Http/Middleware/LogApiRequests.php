<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiLog;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Driver;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $this->logRequest($request, $response);
        return $response;
    }

    protected function logRequest(Request $request, Response $response)
    {
        try {
            $driver = Driver::where('session', $request->header('session'))->first();

            $headers = $request->headers->all();
            $requestBody = $request->all();
            
            // Get response content
            $responseContent = $response->getContent();
            
            // Handle request body - encode to JSON if it's an array
            $encodedRequestBody = is_array($requestBody) ? json_encode($requestBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $requestBody;
            
            // Handle response body - check if it's already JSON
            if ($this->isJson($responseContent)) {
                // If it's already valid JSON, use it as-is without re-encoding
                $encodedResponseBody = $responseContent;
            } else {
                // If it's not JSON, try to encode it
                $encodedResponseBody = json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            
            $responseLength = strlen($encodedResponseBody);

            // Skip response_body if it exceeds the safe length (e.g., 65535 for TEXT)
            $maxLength = 65535;
            $storeResponseBody = $responseLength <= $maxLength ? $encodedResponseBody : null;

            if ($responseLength > $maxLength) {
                Log::warning('Skipped storing response_body due to excessive length', [
                    'url' => $request->fullUrl(),
                    'response_length' => $responseLength,
                    'max_length' => $maxLength,
                    'timestamp' => now()->toDateTimeString()
                ]);
            }

            $log = ApiLog::create([
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => json_encode($headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'request_body' => $encodedRequestBody,
                'response_body' => $storeResponseBody,
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'driver_id' => $driver ? $driver->id : null,
                'created_at' => now(),
            ]);

            Log::debug('API request logged successfully', [
                'log_id' => $log->id,
                'url' => $request->fullUrl(),
                'request_body_length' => strlen($encodedRequestBody),
                'response_body_length' => $responseLength,
                'stored_response' => $storeResponseBody !== null
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log API request', [
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }

    /**
     * Check if string is valid JSON
     */
    protected function isJson($string)
    {
        if (!is_string($string) || trim($string) === '') {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}