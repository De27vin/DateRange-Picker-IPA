<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Services\LanguageService;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function __construct(
        private readonly LanguageService $languageService
    ) {}

    public function switchLang($lang)
    {
        if (!Auth::check() || !session('account.id')) {
            \Log::warning('LanguageController: Missing auth or account session');
            return Redirect::to('/dashboard')->withErrors('Session expired. Please log in again.');
        }

        $enabledLanguages = $this->languageService->getEnabledLanguages();
        $availableLocales = $this->languageService->getAvailableLocales();

        if (array_key_exists($lang, $availableLocales)) {
            $success = $this->languageService->switchUserLanguage(Auth::user(), $lang);
        }

        return Redirect::to('/dashboard')->with('tab', 'changeLanguage');
    }
}
