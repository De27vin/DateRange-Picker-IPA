<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class DisableSourceMaps
{
    public function handle($request, Closure $next)
    {
        // check if its a source map request
        if (str_ends_with($request->getRequestUri(), '.map') ||
            str_contains($request->getRequestUri(), 'sourcemap')) {
            // Return empty 200 response instead of 404
            return new Response('', 200, [
                'Content-Type' => 'application/json',
                'SourceMap' => 'none',
                'X-SourceMap' => 'none'
            ]);
        }

        $response = $next($request);

        // check if we have headers and its a response object we can modify
        if (method_exists($response, 'header')) {
            $response->header('SourceMap', 'none');
            $response->header('X-SourceMap', 'none');
        } elseif (method_exists($response, 'headers')) {
            $response->headers->set('SourceMap', 'none');
            $response->headers->set('X-SourceMap', 'none');
        }

        // add content-type header if its js
        if (str_ends_with($request->getRequestUri(), '.js')) {
            $response->header('Content-Type', 'application/javascript');
        }

        return $response;
    }
}