<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\User;
use App\Models\UsersRole;
use App\Services\LanguageService;
use App\Services\UserContextService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

/** @deprecated */
trait AccountsTrait
{
    protected UserContextService $userContext;
    protected LanguageService $languageService;

    public $account;
    public $accounts;

    public function bootAccountsTrait()
    {
        try {
            $this->userContext = app(UserContextService::class);
            $this->languageService = app(LanguageService::class);

            $context = $this->userContext->initializeAccountContext();

            if (empty($context)) {
                return Redirect::to('/logout');
            }

            $this->account      = $context['account'];
            $this->locale       = $context['locale'];
            $this->profile      = $context['profile'];
            $this->translations = $context['translations'];
        } catch (\Throwable $e) {
            return Redirect::to('/logout');
        }
    }

    public function getCurrentAccount()
    {
        return $this->userContext->getCurrentAccount();
    }

    public function getUserAccounts()
    {
        return $this->userContext->getUserAccounts();
    }

    public function getUsersByAccount()
    {
        $currentAccount = $this->getCurrentAccount();

        $userIds = UsersRole::query()
            ->select('ur_user_id')
            ->where('ur_account_id','=',$currentAccount->account_id)
            ->groupBy('ur_user_id')->get()
            ->pluck('ur_user_id')
            ->toArray();

        $siteUserIds = UsersRole::query()
            ->select('ur_user_id')
            ->whereHas('role', function($query){
                $query->where('roles.role_type','=','site');
            })
            ->get()
            ->pluck('ur_user_id')
            ->toArray();

        $onlyUsersAsIds = array_diff($userIds, $siteUserIds);
        return User::query()
            ->whereIn('user_id',$onlyUsersAsIds)
            ->get();
    }

    public function getSiteUsers()
    {
        $userIds = UsersRole::query()->select('ur_user_id')->whereHas('role', function($query){
            $query->where('roles.role_type','=','site');
        })
        ->get()->pluck('ur_user_id')->toArray();
        return User::query()->whereIn('user_id',$userIds)->get();
    }

    public function getAvailableLocales()
    {
        return $this->languageService->getAvailableLocales();
    }
}