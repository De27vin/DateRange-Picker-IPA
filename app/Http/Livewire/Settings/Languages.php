<?php
namespace App\Http\Livewire\Settings;

use App\Helpers\GroupCache;
use App\Traits\FreeswitchApiTrait;
use Livewire\Component;
use App\Models\Language;
use App\Models\Account;
use App\Models\Locale;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\AccountsTrait;
use App\Traits\TranslationsTrait;

class Languages extends Component
{
    use AccountsTrait;
    use TranslationsTrait;
    use FreeswitchApiTrait;

    public $languagesfromDB;
    public $languages;
    public $availableLocales;
    public $currentDefaultLocale;

    public $languageComments = [];

    public function mount()
    {
        $this->languageComments = [
            "de" => __('German'),
            "fr" => __('French'),
            "en" => __('English'),
            "it" => __('Italian'),
            "es" => __('Spanish'),
            "pt" => __('Portuguese'),
        ];

        $this->availableLocales = $this->getAvailableLocales();
        $this->currentDefaultLocale = $this->account->account_locale_id;
        $this->languages = $this->profile['languages'];
        $this->languagesfromDB = Language::all()->toArray();
    }

    public function render()
    {
        return view('livewire.settings.languages');
    }

    public function toggleLanguageState($language = null)
    {
        $this->profile['languages'][$language] = !$this->profile['languages'][$language];

        $this->saveProfileData($this->profile);
        session(['languages' => $this->profile['languages'], []]);

        $this->makeFsReload();
    }

    public function toggleLanguageDefaultState($language = null)
    {
        if(array_key_exists($language, $this->availableLocales)){
            $this->currentDefaultLocale = $this->availableLocales[$language]['id'];
            $affected = DB::table('accounts')
                ->where('account_id', '=', session('account.id'))
                ->update([ 'account_locale_id' => $this->availableLocales[$language]['id'] ]);

            $this->makeFsReload();
        }
    }

    private function makeFsReload()
    {
        if($result = $this->fsMake('ucp del account ' . session('account.id'), false, true)) {
            $this->notify('success', __('ucp reload account command processed'));
        } else {
            $this->notify('error', __('Due to connection problems, it is possible that the changed values will only take effect after a slight delay.'));
        }
    }
}