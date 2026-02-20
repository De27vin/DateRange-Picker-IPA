<?php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlockTechnicalUserForExternalLink
{
    public function handle(Request $request, Closure $next)
    {
        $isLivewireRoute = Str::contains(url()->current(), '/livewire/message/');
        $isLangSwitch = (request()?->route()?->getName() ?? null) === 'lang.switch';

        if (Auth::check() && !$isLivewireRoute && !$isLangSwitch) {
            $currentRoute = request()?->route()?->getName() ?? null;
            $technicalUserAccount = $this->getAccountSlugIfTechnicalUser(Auth::user());

            if ($technicalUserAccount) {
                $externalLinkRoutes = [
                    $technicalUserAccount.'-external-link',
                    $technicalUserAccount.'-external-link-device',

                    'api-dashboard-basf-devices',

                    'api-equipment-site',
                    'api-equipment-fs-call',

                    'api-data-cfg',
                    'api-data-labels',
                    'api-data-settings',
                    'api-data-countries',
                    'api-data-required',
                    'api-data-translations',
                    'api-assignable-gateways',
                    'api-assignable-sip-numbers',
                ];

                if (!in_array($currentRoute, $externalLinkRoutes)) {
                    return logoutUser();
                }
            }

            if (!$technicalUserAccount) {
                $a = str_contains($currentRoute, '-external-link-device');
                $b = in_array($currentRoute, ['api-dashboard-basf-devices']);
                if ($a || $b) {
                    return logoutUser();
                }
            }
        }

        return $next($request);
    }

    private function getAccountSlugIfTechnicalUser(?Authenticatable $user): bool|string
    {
        if (empty(env('EXTERNAL_LINK_USER')) || empty($extUser = json_decode(env('EXTERNAL_LINK_USER'), true))) {
            return false;
        }

        foreach ($extUser as $accountSlug => $techUser) {
            if ($user?->user_firstname === $techUser['firstname'] && $user?->user_lastname === $techUser['lastname']) {
                return $accountSlug;
            }
        }

        return false;
    }
}