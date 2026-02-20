<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SPXProfilingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('SPX_PROFILING') && function_exists('spx_profiler_start')) {
            spx_profiler_start();
        }

        try {
            $response = $next($request);
        } finally {
            if (env('SPX_PROFILING') && function_exists('spx_profiler_stop')) {
                spx_profiler_stop();
            }
        }

        return $response;
    }
}
