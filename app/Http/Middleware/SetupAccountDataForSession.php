<?php
namespace App\Http\Middleware;

use App\Services\AccountUpdateService;
use App\Traits\AccountsTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class SetupAccountDataForSession
{
    use AccountsTrait;

    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && empty(session('account.id')) && !empty(Cookie::get('ucp_account'))) {
            $this->setAccountSessionData(Cookie::get('ucp_account'));
        }

        return $next($request);
    }
}