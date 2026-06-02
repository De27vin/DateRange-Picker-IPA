<?php
namespace App\Http\Middleware;

use App\Models\Account;
use App\Services\AccountUpdateService;
use Carbon\Carbon;
use Closure;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SynchronizeJsonProfile
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && $request->route()->getName() === 'accounts') {
            foreach (app(\App\Services\UserContextService::class)->getUserAccounts() as $account) {
                (new AccountUpdateService($account))->synchronizeAccountJsonSchema();
            }
        }

        return $next($request);
    }
}