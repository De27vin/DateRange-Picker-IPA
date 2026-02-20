<?php
namespace App\Http\Livewire;

use App\Models\Device;
use App\Models\DeviceSite;
use App\Traits\AccountsTrait;
use App\Traits\AlertsTrait;
use App\Traits\DevicesTrait;
use App\Traits\TranslationsTrait;
use App\Traits\SearchFiltersTrait;
use Livewire\Component;

class UcpComponent extends Component
{
    use AccountsTrait;
    use AlertsTrait;
    use TranslationsTrait;
    use DevicesTrait;
    use SearchFiltersTrait;

    public $profile;
    public $locale;
    public $account;
    public $activeAlarm;
    public $envIsLocal;
    public $pages;
    public $deviceIddd;
    public $currentPage;
    public $filters;
    public $scope;
    public $deviceStates;
    public $translations;

    // values for pagination
    public $pageNumber;
    public $prevPage;
    public $nextPage;
    public $lastPage;
    public $hasMorePages;
    protected $listeners = [
        'goto' => 'routeToPage',
        'loadDeviceStates' => 'loadDeviceStates'
    ];


    public function booted()
    {
        $this->pages = config('ucp.pages');
        // $this->filters = session('deviceSearchFilter', null);
    }

    public function routeToPage($page = 'ucp.dashboard', $pageScope = 'enabled')
    {
        ray('in route to page ' . $page);
        $pageRoutes = [
            'dashboard' => ['dashboard' => true, 'ucp.dashboard' => true, '/dashboard' => true],
            'devices' => ['devices' => true, 'ucp.devices' => true, '/devices' => true],
            'devices-phone' => ['devices-phone' => true, 'ucp.devices-phone' => true, '/devices-phone' => true],
            'device' => ['device' => true, 'ucp.device' => true, '/device' => true, 'device-details' => true, 'ucp.device-details' => true],
            'logout' => ['logout' => true, 'exit' => true, '/logout' => true],
        ];
        // ray($pageScope);

        switch(true){
            case isset($pageRoutes['dashboard'][$page]):
                $this->routeToDashboard();
            break;
            case isset($pageRoutes['devices'][$page]):
                $this->routeToDevices($pageScope);
            break;
            case isset($pageRoutes['devices-phone'][$page]):
                $this->routeToDevicesPhone($pageScope);
            break;
            case isset($pageRoutes['device'][$page]):
                $this->routeToDevice( intval($pageScope) );
            break;
            case isset($pageRoutes['logout'][$page]):
                $this->routeToLogout();
            break;
            default:
                ray('routeDirectToPage ' . $page);
                $this->routeDirectToPage($page);
            break;
        }
    }

    public function routeDirectToPage($page)
    {
        $this->pages = config('ucp.pages');
        if(array_key_exists($page, $this->pages)){
            $this->currentPage = $this->pages[$page];
            session(['slug' => $this->currentPage['slug']]);
            $this->emit('switch', $page);
        } else {
            $this->routeToDashboard();
        }
    }

    public function prepareSearchDevicesFromNavigation($filterString = null)
    {
        // $this->resetDeviceSearchFilter(false);
        switch($filterString){
            case 'enabled':
                $filterKey = 'device_enabled';
            break;
            case 'disabled':
                $filterKey = 'device_disabled';
            break;
            case 'deleted':
                $filterKey = 'device_deleted';
            break;
            case 'warning':
                $filterKey = 'device_has_warning';
            break;
            case 'error':
                $filterKey = 'device_has_error';
            break;
            case 'overdue':
                $filterKey = 'overdue';
            break;
            default:
                $filterKey = $filterString;
            break;
        }
        // ray($this->filters);
        // $search = $this->filters['search'];
        $this->resetDeviceSearchFilter();
        return $filterKey;
    }

    // public function routeToSearchDevicesFromDevices()
    // {
    //     $this->pages = config('ucp.pages');
    //     $this->currentPage = $this->pages['ucp.devices'];
    //     session(['slug' => $this->currentPage['slug']]);
    //     $search = $this->filters['search'];
    //     $this->resetDeviceSearchFilter();
    //     $this->updateDeviceSearchFilter(['search' => $search]);
    //     $this->filters = session('deviceSearchFilter', null);
    //     $this->emit('switch', 'ucp.devices');
    // }

    public function routeToLogout()
    {
        $this->emit('switch', 'logout');
    }

    private function routeToDashboard()
    {
        $this->emit('switch', 'ucp.dashboard');
    }

    private function routeToDevicesPhone($phonenumber = null)
    {
        if($phonenumber == null){
            $this->routeToDevices('device_enabled');
        } else {
            $deviceSite = DeviceSite::query()
                ->with('numbers', 'devices')
                ->where('ds_account_id','=',session('account.id'))
                ->whereHas('numbers', function($q) use($phonenumber) {
                    $q->where('numbers.number_value','=',$phonenumber);
                })
                ->first();
            $device = $deviceSite->devices->first();
            session(['currentDevice' => $this->getDeviceDetails($device->device_id)]);
            $this->emit('switch', 'ucp.device-details');
        }
    }

    private function routeToDevices($filterString = null)
    {
        if($this->scope == 'devices'){
            ray('search from devices');
            ray($filterString);
            $this->filters = session('deviceSearchFilter', null);
            if($this->filters == null){
                $this->filters = $this->initDeviceSearchFilter();
            }
        } else {
            ray('search from navigation');
            ray($filterString);
            $this->filters = session('deviceSearchFilter', null);
            if($this->filters == null){
                $this->filters = $this->initDeviceSearchFilter();
            }
            $search = $this->filters['search'];
            $this->filters['tab'] = '';
        }
        $filterKey = $this->prepareSearchDevicesFromNavigation($filterString);
        ray($filterKey);
        if($filterKey == 'search'){
            $this->updateDeviceSearchFilter(['device_enabled' => false, 'search' => $search, 'tab' => null]);
        } else {
            $this->updateDeviceSearchFilter(['device_enabled' => false, 'search' => $search, $filterKey => true]);
        }
        $this->filters = session('deviceSearchFilter', null);

        $this->emit('switch', 'ucp.devices');
    }

    public function routeToDevice($deviceId = null)
    {
        if($deviceId == null || !is_int($deviceId) ){
            $this->routeToDevices('device_enabled');
        } else {
            session(['currentDevice' => $this->getDeviceDetails($deviceId)]);
            $this->emit('switch', 'ucp.device-details');
        }
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

    public function info($message)
    {
        $messages = [
            'A blessing in disguise',
            'Bite the bullet',
            'Call it a day',
            'Easy does it',
            'Make a long story short',
            'Miss the boat',
            'To get bent out of shape',
            'Birds of a feather flock together',
            "Don't cry over spilt milk",
            'Good things come',
            'Live and learn',
            'Once in a blue moon',
            'Spill the beans',
        ];
        echo $this->notify($messages[array_rand($messages)]);
    }


}