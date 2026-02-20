<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\Locale;
use App\Models\User;
use App\Models\UsersRole;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use App\Exceptions\UcpException;
use App\Services\AccountUpdateService;

trait AccountsTrait
{

    public $account;
    public $accounts;


    public function bootAccountsTrait()
    {
        try {
            $this->envIsLocal   = (Str::afterLast($_SERVER['HTTP_HOST'], '.') == 'local');
            $this->account      = Account::findOrFail(session('account.id'));
            $this->locale       = session('locale','en');
            $this->profile      = $this->account->account_translation;
            $this->translations = session('translations.'.$this->locale,[]);
            $this->activeAlarm = [
                'deviceId' => null,
                'show' => false
            ];
        } catch (\Throwable $e) {
            return Redirect::to('/logout');
        }
    }

    public function getCurrentAccount()
    {
        try {
            return Account::select('account_id','account_name','account_slug','account_enabled')->findOrFail(session('account.id'));
        } catch (\Exception $e) {
            $this->notify($e->getMessage());
            return null;
        }
    }

    public function getUserAccounts()
    {
        return Auth::user()->accounts;
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

    public function setAccountSessionData($id)
    {
        $account = Account::find($id);
        if (!$account) {
            logoutUser();
        }

        $sessionAccount = [
            'id' => $id,
            'slug' => ($account->account_slug ? $account->account_slug : 'system')
        ];
        
        $profileData            = $account->account_translation;
        session(['account'      => $sessionAccount]);
        session(['searchTerm'   => null]);
        session(['alarm'        => ['deviceId' => null, 'show' => false]]);
        session(['translations' => $profileData['translations'], []]);
        session(['languages'    => $profileData['languages'], []]);
        session(['config'       => $profileData['config'], []]);
        $userLanguge            = $this->getUserLanguage();
        session(['locale'       => $userLanguge]);
        $this->profile          = $profileData;

        // $this->initDeviceSearchFilter();
    }

    public function getUserLanguage()
    {
        $account = Account::findOrFail(session('account.id'));
        if($account->account_locale_id != null){
            $accountLocale = Locale::with('language')
                ->where('locale_parent_id','=',null)
                ->where('locale_id','=',$account->account_locale_id)
                ->first();
             $accountLanguage = $accountLocale?->language?->language_code;
        } else {
            $availableLocales = $this->getAvailableLocales();
            if(array_key_exists('en', $availableLocales)){
                $account->account_locale_id = $availableLocales['en']['id'];
                $account->save();
            }
            $accountLanguage = 'en';
        }
        if(Auth::user()->user_locale_id != null){
            $userLocale = Locale::with('language')
                ->where('locale_parent_id','=',null)
                ->where('locale_id','=',Auth::user()->user_locale_id)
                ->first();
            $userLanguage = $userLocale->language->language_code;
        } else {
            $userLanguage = $accountLanguage;
        }
        return $userLanguage;
    }

    public function getAvailableLocales()
    {
        return Locale::query()
            ->where('locale_parent_id','=',null)
            ->get()
            ->pluck('locale_id','locale_code')
            ->mapWithKeys(function($key,$item){
                $langugeCode = Str::before($item,'_');
                return [$langugeCode => ['id' => $key, 'code' => $langugeCode]];
            })
            ->unique('code')
            ->toArray();
    }

    public function addLanguage()
    {
        $currentLanguages = $this->profile['languages'];
        foreach ($this->languages as $language) {
            if(!array_key_exists($language->language_code, $currentLanguages)){
                $this->profile['translations'][$language->language_code] = $this->profile['translations']['default'];
                $this->profile['languages'][$language->language_code] = false;
            }
        }
        // ray($this->translations);
        $this->saveProfileData($this->profile);
    }

    public function resetPaginationData($itemsPerPage = 20)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->pageNumber = 1;
        $this->prevPage = 1;
        $this->nextPage = 1;
        $this->lastPage = 1;
        $this->hasMorePages = false;
    }

    // public function updatePaginationData($pageNumber = 1, $prevPage = 1, $nextPage = 1, $lastPage = 1, $hasMorePages = false)
    public function updatePaginationData($data, $pageNumber = 1)
    {
        $this->pageNumber = $pageNumber;
        $this->prevPage = ($pageNumber > 1 ? $pageNumber-1 : 1);
        $this->nextPage = ($data->hasMorePages() ? $pageNumber+1 : $pageNumber);
        $this->lastPage = $data->lastPage();
        $this->hasMorePages = $data->hasMorePages();
    }


    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logoutActiveUser()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }


}