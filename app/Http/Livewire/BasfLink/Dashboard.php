<?php

namespace App\Http\Livewire\BasfLink;

use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Models\Account;
use App\Models\Device;
use App\Models\DeviceLabelOld;
use App\Models\User;
use App\Services\CustomFieldsService;
use App\Services\DeviceAlertsService;
use App\Services\SearchDeviceService;
use App\Services\UserContextService;
use App\Traits\SearchFiltersTrait;
use App\Traits\TranslationsTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    use WithPerPagePagination;
    use SearchFiltersTrait;
    use TranslationsTrait;

    public $export;
    public $alerts;
    public $alertsCountGrouped;
    public $defaultTabs = [];
    public $fieldTranslations;
    public $alertTranslations;


    // search/filter functionality
    public $groups;
    public $filters;
    public $sortOptions = [];
    public $searchOptions = [];
    public $searchSelected = ['all'];
    public $searchTabs = [
        'active alarm' => false,
        'overdue' => true,
        'alert' => false,
    ];
    public $filtersId = 'Dashboard';

    public $extLinkAccSlug = null;

    private SearchDeviceService $searchService;
    private DeviceAlertsService $alertsService;
    private CustomFieldsService $customService;
    private UserContextService $userContext;

    public function __construct($id = null) {
        parent::__construct($id);
        $this->alertsService = new DeviceAlertsService();
        $this->searchService = new SearchDeviceService();
        $this->customService = new CustomFieldsService();
        $this->userContext = app(UserContextService::class);
    }

    public function mount()
    {
        if (empty(env('EXTERNAL_LINK_URL')) || empty($extLink = json_decode(env('EXTERNAL_LINK_URL'), true))) {
            \Log::error('External link url variable is not defined or malformed', ['file' => __DIR__.'/'.__FILE__.':'.__LINE__]);
            abort(404);
        }
        if (empty(env('EXTERNAL_LINK_USER')) || empty($extUser = json_decode(env('EXTERNAL_LINK_USER'), true))) {
            \Log::error('External link user variable is not defined or malformed', ['file' => __DIR__.'/'.__FILE__.':'.__LINE__]);
            abort(404);
        }
        foreach ($extLink as $account => $link) {
            if ($link === request()->getPathInfo()) {
                $this->extLinkAccSlug = $account;
            }
        }

        if(empty($this->extLinkAccSlug) || empty($extUser[$this->extLinkAccSlug])) {
            \Log::error("Account slug or external user for slug is not found", ['file' => __DIR__.'/'.__FILE__.':'.__LINE__]);
            abort(404);
        }


        $extLinkTechUser = User::where(['user_firstname' => $extUser[$this->extLinkAccSlug]['firstname'], 'user_lastname' => $extUser[$this->extLinkAccSlug]['lastname']])->first();
        $extLinkAccount = Account::where('account_slug', $this->extLinkAccSlug)->first();
        if (empty($extLinkTechUser) || empty($extLinkAccount)) {
            \Log::error("Account or Technical User for slug is not found", ['user' => $extUser, 'acc_slug' => $accountSlug, 'file' => __DIR__.'/'.__FILE__.':'.__LINE__]);
            abort(404);
        }
        Auth::login($extLinkTechUser);
        $this->userContext->switchAccount($extLinkAccount->account_id);

        $agent = Auth::user()->isAgent;
        $mobile = Auth::user()->isMobile;

        $this->defaultTabs = match (true) {
            $agent && $mobile => ['active alarm' => true, 'overdue' => true, 'alert' => true],
            $agent => ['active alarm' => true, 'overdue' => true, 'alert' => false],
            $mobile => ['active alarm' => false, 'overdue' => true, 'alert' => true],
            default => ['active alarm' => false, 'overdue' => true, 'alert' => false],
        };
        $this->searchTabs = $this->defaultTabs;

        $this->perPage = 200;

        $this->fieldTranslations = $this->getFieldTranslations($this->locale ?? session('locale', 'de'));
        $this->alertTranslations = $this->getAlertTranslations($this->locale ?? session('locale', 'de'));

    }


    public function render()
    {
        return view('livewire.basf_link.dashboard')->layout('layouts.basf_link');
    }

}
