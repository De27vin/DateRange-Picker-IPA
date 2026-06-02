<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class InternalApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('app.internal_api_token');
        
        if (empty($expectedToken)) {
            Log::error('InternalApiToken middleware: INTERNAL_API_TOKEN not configured');
            return response()->json(['error' => 'Internal API token not configured'], 500);
        }
        
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader) {
            Log::warning('InternalApiToken middleware: Missing Authorization header', [
                'ip' => $request->getClientIp(),
                'url' => $request->url()
            ]);
            return response()->json(['error' => 'Missing Authorization header'], 401);
        }
        
        if (!str_starts_with($authHeader, 'Bearer ')) {
            Log::warning('InternalApiToken middleware: Invalid Authorization header format', [
                'ip' => $request->getClientIp(),
                'url' => $request->url()
            ]);
            return response()->json(['error' => 'Invalid Authorization header format'], 401);
        }
        
        $providedToken = substr($authHeader, 7); // Remove "Bearer "
        
        if (!hash_equals($expectedToken, $providedToken)) {
            Log::warning('InternalApiToken middleware: Invalid token', [
                'ip' => $request->getClientIp(),
                'url' => $request->url()
            ]);
            return response()->json(['error' => 'Invalid token'], 403);
        }
        
        return $next($request);
    }
}
