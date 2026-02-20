<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\Language;
use App\Models\Locale;
use Illuminate\Support\Facades\DB;
use App\Traits\AccountsTrait;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    use AccountsTrait;

    public function switchLang($lang)
    {
        // Debug logging
        \Log::info('LanguageController@switchLang called', [
            'lang' => $lang,
            'auth_check' => Auth::check(),
            'user_id' => Auth::check() ? Auth::user()->user_id : null,
            'session_account_id' => session('account.id'),
            'cookie_account' => \Cookie::get('ucp_account')
        ]);

        // Check if user is authenticated and has account session
        if (!Auth::check() || !session('account.id')) {
            \Log::warning('LanguageController: Missing auth or account session');
            return Redirect::to('/dashboard')->withErrors('Session expired. Please log in again.');
        }

        $languages = Language::query()->where('language_enabled','=',true)->pluck('language_code')->toArray();
        $availableLocales = $this->getAvailableLocales();

        \Log::info('LanguageController: Processing language switch', [
            'enabled_languages' => $languages,
            'available_locales' => $availableLocales,
            'lang_exists' => array_key_exists($lang, $availableLocales)
        ]);

        if (array_key_exists($lang, $availableLocales)) {
            $locale_id = $availableLocales[$lang]['id'];
            $affected = DB::table('users')
              ->where('user_id', '=', Auth::user()->user_id)
              ->update(['user_locale_id' => $locale_id]);
            Session::put('locale', $lang);
            
            \Log::info('LanguageController: Language updated', [
                'locale_id' => $locale_id,
                'affected_rows' => $affected,
                'session_locale' => Session::get('locale')
            ]);
        } else {
            \Log::warning('LanguageController: Language not found', [
                'requested_lang' => $lang,
                'available_keys' => array_keys($availableLocales)
            ]);
        }

        // Use safe redirect to dashboard instead of back() to avoid API route collisions
        return Redirect::to('/dashboard')->with('tab', 'changeLanguage');
    }
}
