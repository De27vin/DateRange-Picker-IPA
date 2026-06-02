<?php
namespace App\Http\Middleware;

use App\Services\UserContextService;
use Closure;
use Illuminate\Http\Request;

class SetupAccountDataForSession
{
    public function __construct(
        private readonly UserContextService $userContext
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $this->userContext->syncAccountSessionFromCookie();
            $this->userContext->syncAccountCookieFromSession();

            if (!$this->userContext->ensureAccountContext()) {
                return redirect('/logout')->with('error', 'No accounts assigned to your user');
            }
        }

        return $next($request);
    }
}