<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** @deprecated - BlockTechnicalUserForExternalLink is doing same but more configurable */
class BlockBasfTechnicalUser
{
    public function handle(Request $request, Closure $next)
    {
        $isLivewireRoute = Str::contains(url()->current(), '/livewire/message/');
        $isLangSwitch = (request()?->route()?->getName() ?? null) === 'lang.switch';

        if (Auth::check() && !$isLivewireRoute && !$isLangSwitch) {
            $currentRoute = request()?->route()?->getName() ?? null;
            $technicalUserRoutes = [
                'basf-link',
                'basf-link-device',
            ];

            $user = Auth::user();
            $isBasfTechnicalUser = $user?->user_firstname === 'Basf' && $user?->user_lastname === 'Technical';

            if ($isBasfTechnicalUser && !in_array($currentRoute, $technicalUserRoutes)) {
                return logoutUser();
            }
            if (!$isBasfTechnicalUser && in_array($currentRoute, ['basf-link-device'])) {
                return logoutUser();
            }
        }

        return $next($request);
    }
}