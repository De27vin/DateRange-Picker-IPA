<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->ajax() && !$request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        return $next($request);
    }
}
