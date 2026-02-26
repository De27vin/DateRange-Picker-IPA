<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class RequestContextLoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = $this->resolveRequestId($request);
        $request->attributes->set('request_id', $requestId);
        Log::withContext(['request_id' => $requestId]);

        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $handler = app(ExceptionHandlerContract::class);
            $handler->report($e);
            $response = $handler->render($request, $e);
        }

        $response->headers->set('X-Request-Id', $requestId);
        $this->logApiRequest($request, $requestId, $response->getStatusCode(), $start);

        return $response;
    }

    private function resolveRequestId(Request $request): string
    {
        $incoming = (string) $request->headers->get('X-Request-Id', '');
        if (Str::isUuid($incoming)) {
            return $incoming;
        }

        return (string) Str::uuid();
    }

    private function logApiRequest(Request $request, string $requestId, int $status, float $start): void
    {
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        Log::channel('ipa')->info('api.request', [
            'event' => 'api.request',
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->getPathInfo(),
            'status' => $status,
            'duration_ms' => $durationMs,
            'ip' => $request->ip(),
        ]);
    }
}
