<?php

namespace App\Http\Livewire\Ucp;

use App\Exceptions\UcpException;
use App\Exports\HistoryExport;
use App\Helpers\Ucp;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSorting;
use App\Models\Device;
use App\Http\Livewire\UcpComponent;
use App\Models\DeviceAlert;
use App\Models\DeviceSite;
use App\Models\Event;
use App\Models\Session;
use App\Models\SessionType;
use App\Services\FileRecordsService;
use App\Services\SessionHistoryService;
use App\Traits\SearchFiltersTrait;
use App\Traits\DeviceFormTrait;
use App\Traits\FreeswitchApiTrait;
use App\Traits\TranslationsTrait;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use App\Models\Account;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class DeviceHistoryNew extends UcpComponent
{
    use FreeswitchApiTrait;
    use DeviceFormTrait;
    use SearchFiltersTrait;
    use WithSorting;
    use WithBulkActions;
    use WithPerPagePagination;
    use WithCachedRows;
    use TranslationsTrait;

    /** @var Device */
    public $device;
    /** @var DeviceSite */
    public ?DeviceSite $deviceSite = null;
    public $siteDevicesIds = [];

    public $exportActive = false;

    public $monitorActiveTask;
//    public $historyHeaders = [];
    public $historyDetails;
    public $sessionsPerPage = 30;
    public $currentSessionsAmount;

    public $historyFilter = [];
    public $openedSessionsDetails = [];
    public $historyVisibility;
    public $severityFilter;
    public $dateFilter;
    public $requestedSessionId = null;

    public $relatedEvents = [];
    public $showRelatedEvents = false;

    public $deviceTasks;

    public $contextOptions = [];
    public $context;

    public $leftGap = true;

    public $pendingSetRevivalSessions = [];

    public $exportFormat = 'csv';


    // classification monitoring
    public $monitorClassification = false;
    public $unclassifiedAlarmSessionId = null;
    // classification monitoring

    // those seem to be deprecated for now - maybe later when cache will be recovered they can be used
    public $historyCache = null;
    public $skipHistoryUpdate = false;

    protected $listeners = [
        'exportHistory',
    ];

    private SessionHistoryService $sessionHistoryService;

    private FileRecordsService $fileRecordsService;

//    private $queryLog = [];
//
//    public function boot()
//    {
//        DB::listen(function($query) {
//            $this->queryLog[] = [
//                'sql' => $query->sql,
//                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
//                'time' => microtime(true)
//            ];
//        });
//    }
//
//    public function dehydrate()
//    {
//        if (count($this->queryLog) > 20) {
//            \Log::channel('query')->info('Query Log for request:', [
//                'queries' => collect($this->queryLog)->map(function($log) {
//                    return [
//                        'sql' => $log['sql'],
//                        'caller' => collect($log['trace'])->take(3)->map(function($trace) {
//                            return ($trace['class'] ?? '') . ($trace['function'] ?? '');
//                        })->implode(' -> ')
//                    ];
//                })->toArray()
//            ]);
//        }
//    }


    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->sessionHistoryService = new SessionHistoryService();
        $this->fileRecordsService = new FileRecordsService();
    }

    /** @deprecated */
    public function checkExportProgress()
    {
        return $this->getExportProgress();
    }

    public function mount(?int $deviceId, ?int $deviceSiteId, bool $leftGap = true, bool $monitorClassification = false)
    {
        $this->monitorClassification = $monitorClassification;

        // this is maybe needed to approach differently
        $this->leftGap = $leftGap;
        // this is maybe needed to approach differently

        $startParam = request()->query('startHistory');
        $endParam = request()->query('endHistory');
        $sessionParam = request()->query('session');

        $this->device = $deviceId ? Device::find($deviceId) : null;
        $this->deviceSite = $deviceSiteId ? DeviceSite::find($deviceSiteId) : null;

        if ($this->deviceSite) {
            $this->siteDevicesIds = $this->deviceSite->devices->pluck('device_id', 'device_equipment')->toArray();
            $this->context = 'all';
            $this->contextOptions = [
                'all' => __('Site and all devices'),
                'only_site' => __('Site without devices'),
            ];
            foreach ($this->siteDevicesIds as $eq => $id) {
                $this->contextOptions[$id] = __('Device: 🏷️:eq', ['eq' => $eq]);
            }
        } else {
            // this is kinda workaround to make id equipment map working and display equipment in list
            $this->siteDevicesIds = [$this->device->device_equipment => $this->device->device_id];
        }

        $this->perPage = $this->sessionsPerPage;
        $this->currentSessionsAmount = $this->sessionsPerPage;

        $this->initHistoryFilters($startParam, $endParam);
        // to delete - this is not used - test first
        $this->initHistoryData();
//        $this->updateHistoryHeaderData();

        if (!empty($sessionParam)) {
            $this->requestedSessionId = $sessionParam;
//            $this->toggleHistoryVisibility($sessionParam);
        }

        // PORT FROM DETAILS
        $this->deviceTasks = [
            'carcall' => false,
            'set'     => false,
            'revival' => false,
            'export'  => false
        ];

        if ($this->monitorClassification) {
            $this->initializeClassificationMonitoring();
        }
    }

    public function render()
    {
        return view('livewire.ucp.device-history-new-new', [
            'history' => $this->history
        ]);
    }

    public function updatedContext($context)
    {
        if (is_numeric($context)) {
            $this->device = Device::find($context);
        }
        $this->initHistoryData();
    }

    public function resetFilters()
    {
        $this->dateFilter     = $this->resetDateFilter();
        $this->historyFilter  = $this->resetHistoryFilter();
        $this->severityFilter = $this->resetSeverityFilter();
        $this->historyCache = null;
        Cache::forget($this->getHistoryCacheKey());
    }

    public function initHistoryFilters(\DateTime|string|null $startDate = null, \DateTime|string|null $endDate = null)
    {
        $this->dateFilter     = $this->initDateFilter($startDate, $endDate);
        $this->locale         = $this->getLocale();
        $this->severityFilter = $this->getSeverityFilter();
        $this->historyFilter  = $this->getHistoryFilter();
        $this->historyDetails = [];
        $this->historyCache = null;
        Cache::forget($this->getHistoryCacheKey());
    }

    public function toggleSeverityFilter($severity)
    {
        $this->severityFilter = $this->updateSeverityFilter($severity);
        $this->historyCache = null;
        Cache::forget($this->getHistoryCacheKey());
        //
        // THIS historyHeaders are not in active use
//        $this->updateHistoryHeaderData();
    }

    // deprecated
//    public function setHistoryFilter($activeFilter)
//    {
//        $this->historyFilter = $this->updateHistoryFilter($activeFilter);
//        $this->updateHistoryHeaderData();
//    }

    private function initializeClassificationMonitoring()
    {
        $latestUnclassifiedAlarm = Session::query()
            ->whereHas('session_type', fn($query) => $query->where('st_type', 'ALARM'))
            ->with(['events' => function($query) {
                $query->whereHas('event_type', fn($q) => $q->where('et_type', 'CLASS'));
            }])
            ->where(function($query) {
                if ($this->deviceSite && $this->context === 'all') {
                    $query->where('session_ds_id', $this->deviceSite->ds_id)
                        ->orWhereIn('session_device_id', $this->siteDevicesIds);
                } elseif ($this->deviceSite && $this->context === 'only_site') {
                    $query->where('session_ds_id', $this->deviceSite->ds_id)
                        ->where('session_device_id', null);
                } elseif ($this->device) {
                    $query->where('session_device_id', $this->device->device_id);
                }
            })
            ->latest('session_start')
            ->first();

        if ($latestUnclassifiedAlarm) {
            $hasClassification = $latestUnclassifiedAlarm->events
                ->filter(fn ($event) => $event->event_type->et_type == 'CLASS')
                ->first() !== null;

            if (!$hasClassification) {
                $this->unclassifiedAlarmSessionId = $latestUnclassifiedAlarm->session_id;
            }
        }
    }

    public function toggleHistoryFilter($activeFilter)
    {
        if (isset($this->historyFilter[$activeFilter])) {
            $this->historyFilter[$activeFilter] = !$this->historyFilter[$activeFilter];
        }

        $this->historyFilter = $this->updateHistoryFilter($activeFilter, $this->historyFilter[$activeFilter]);
        $this->historyCache = null;
        Cache::forget($this->getHistoryCacheKey());
        // THIS historyHeaders are not in active use
//        $this->updateHistoryHeaderData();
    }

    public function getHistoryProperty()
    {
        /** @var LengthAwarePaginator $pagination */
        $pagination = $this->applyPagination($this->getSessionsQuery());
        $account = Account::findOrFail(session('account.id'));

        foreach ($pagination->items() as $session) {
            if (!empty($session->session_host)) {
                $recordPath = $this->fileRecordsService->getExistingRecording($session, $account->account_slug);
                if (!empty($recordPath)) {
                    $session->downloadable_record = $recordPath;
                }
            }
        }

        return $pagination;
    }

    // this is not used anymore I think
    private function getHistoryCacheKey(): string
    {
        return sprintf(
            'history_%s_%s_%s_%s_%s_%s_%s_%d',
            $this->context ?? 'null',
            $this->device?->device_id ?? 'null',
            $this->deviceSite?->ds_id ?? 'null',
            md5(serialize($this->siteDevicesIds ?? [])),
            md5(serialize($this->historyFilter)),
            md5(serialize($this->dateFilter)),
            md5(serialize($this->severityFilter)),
            $this->perPage
        );
    }

    // MOVED FROM DEVICE DETAILS - COMPLETED
//    public function toggleHistoryVisibility($sessionId)
//    {
//        $this->historyVisibility[$sessionId] = !$this->historyVisibility[$sessionId];
//        $openedSessions = array_filter($this->historyVisibility);
//        if (array_key_exists($sessionId, $openedSessions)) {
//            $this->updateSessionDetails($openedSessions);
//        }
//        // WHY THIS IS NEEDED - MAKE EVENT IF NEEDED
////        $this->updateDeviceStats();
//
//        $this->skipHistoryUpdate = true;
//    }

    public function updateHistory()
    {
        /** @var LengthAwarePaginator $history */
        $history = $this->history;
        $sessions = $history->getIterator();

        foreach ($sessions as $session) {
            if (isset($this->historyVisibility[$session->session_id])) {
                break;
            }
            $this->historyVisibility[$session->session_id] = false;

            if (in_array($session->session_type->st_type, ['SET', 'REVIVAL']) &&
                (!$session->session_complete || is_null($session->session_end))) {
                $this->pendingSetRevivalSessions[$session->session_id] = true;
            }

            $this->emit('updateDeviceStats');
            $this->emit('initDeviceSiteAlertsAndStates');
        }

        if (!empty($this->pendingSetRevivalSessions)) {
            $pendingSessionIds = array_keys($this->pendingSetRevivalSessions);
            $completedSessions = Session::whereIn('session_id', $pendingSessionIds)
                ->where('session_complete', true)
                ->whereNotNull('session_end')
                ->get();

            foreach ($completedSessions as $session) {
                unset($this->pendingSetRevivalSessions[$session->session_id]);
                $this->dispatchBrowserEvent('refreshSiteTile');
                break;
            }
        }

        if ($this->monitorClassification) {
            $this->checkForClassificationChanges($sessions);
        }

        $openedSessions = array_filter($this->historyVisibility);
        $this->updateSessionDetails($openedSessions);

//        $this->emit('updateDeviceStats');
//        $this->emit('initDeviceSiteAlertsAndStates');
    }

    private function checkForClassificationChanges($sessions)
    {
        // This might become useful at some point
        // If we're not tracking any unclassified alarm, look for a new one
//        if ($this->unclassifiedAlarmSessionId === null) {
//            foreach ($sessions as $session) {
//                if ($session->session_type->st_type === 'ALARM') {
//                    $hasClassification = $session->events
//                        ->filter(fn ($event) => $event->event_type->et_type == 'CLASS')
//                        ->first() !== null;
//
//                    if (!$hasClassification) {
//                        $this->unclassifiedAlarmSessionId = $session->session_id;
//                        return; // Found new unclassified alarm to monitor
//                    }
//                    break; // Only check the latest alarm session
//                }
//            }
//            return;
//        }

        // Check if our tracked unclassified alarm got classified
        foreach ($sessions as $session) {
            if ($session->session_id === $this->unclassifiedAlarmSessionId) {
                $classification = $session->events
                    ->filter(fn ($event) => $event->event_type->et_type == 'CLASS')
                    ->first()?->event_value;

                if ($classification) {
                    // Alarm got classified! Notify and reset tracking
                    $this->dispatchBrowserEvent('alarmClassified', [
                        'sessionId' => $session->session_id,
                        'classification' => $classification
                    ]);
                    $this->unclassifiedAlarmSessionId = null;
                }
                break;
            }
        }
    }

    // to delete - this is not used - test it first
    public function initHistoryData()
    {
        $sessions = Session::query();

        // all option
        if ($this->deviceSite && $this->context === 'all') {
            $sessions->where('session_ds_id', $this->deviceSite->ds_id)
                ->orWhereIn('session_device_id', $this->siteDevicesIds);
        }
        // only_site option
        elseif ($this->deviceSite && $this->context === 'only_site') {
            $sessions->where('session_ds_id', $this->deviceSite->ds_id)
                ->where('session_device_id', null);
        }
        // device option
        elseif ($this->device) {
            $sessions->where('session_device_id', '=', $this->device->device_id);
        }

        $sessions = $sessions
            ->with('events', 'events.event_type')
            ->orderByDesc('session_id')
            ->pluck('session_id');

        $this->monitorActiveTask = false;
        $this->historyVisibility = [];
        foreach ($sessions as $session_id) {
            $this->historyVisibility[$session_id] = false;
        }
        $this->openedSessionsDetails = [];
    }
    // MOVED FROM DEVICE DETAILS - COMPLETED

    public function getSessionsQuery()
    {
        $this->historyFilter  = $this->getHistoryFilter();
        $this->severityFilter = $this->getSeverityFilter();
        $startDate            = $this->getStartDate($this->dateFilter['dateFromValue'] ?? null);
        $endDate              = $this->getEndDate($this->dateFilter['dateToValue'] ?? null);

        $alarmSessionTypeId   = SessionType::query()->where('st_type','=','ALARM')->first()->st_id;

        $query = Session::with([
            'session_type',
            'session_direction',
            'comments',
        ]);

        // all option
        if ($this->deviceSite && $this->context === 'all') {
            $query->where(function ($query) {
                $query->where('session_ds_id', $this->deviceSite->ds_id)->orWhereIn('session_device_id', $this->siteDevicesIds);
            });
        }
        // only_site option
        elseif ($this->deviceSite && $this->context === 'only_site') {
            $query->where('session_ds_id', $this->deviceSite->ds_id)
                ->where('session_device_id', null);
        }
        // device option
        elseif ($this->device) {
            $query->where('session_device_id', '=', $this->device->device_id);
        }

        $query
            ->whereBetween('session_start', [$startDate, $endDate])
            ->with('events', 'events.event_type')
//            ->when($this->exportActive, function($query) {
//                return $query->with('events', 'events.event_type');
//            })
            ->when(!empty(array_filter($this->historyFilter)), function($query) {

                $filterTypesMap = [
                    'alarms' => ['ALARM'],
                    'carcalls' => ['CARCALL'],
                    'periodicals' => ['PERIODICAL', 'MONITOR'],
                    'sets' => ['SET'],
                    'revivals' => ['REVIVAL'],
                    'triggers' => ['TRIGGER'],
                    'calls' => ['CALL'],
                ];
                $filters = array_filter($this->historyFilter);
                $types = [];
                foreach (array_intersect_key($filterTypesMap, $filters) as $array) {
                    $types = array_merge($types, $array);
                }

                return $query->with('alerts', 'sets', 'comments')->types($types);
            })
//            ->when($this->historyFilter['calls'], function($query) {
//                return $query->calls();
//            })
//            ->when($this->historyFilter['carcalls'], function($query) {
//                return $query->carCalls();
//            })
//            ->when($this->historyFilter['periodicals'], function($query) {
//                return $query->with('alerts')->alerts();
//            })
//            ->when($this->historyFilter['sets'], function($query) {
//                return $query->with('sets')->sets();
//            })
//            ->when($this->historyFilter['revivals'], function($query) {
//                return $query->with('sets')->revivals();
//            })
//            ->when($this->historyFilter['triggers'], function($query) {
//                return $query->with('sets')->triggers();
//            })
            ->when(empty(array_filter($this->historyFilter)), function($query) {
                return $query->with('alerts', 'sets');
            })
            ->when($this->severityFilter['warnings'], function($query) use ($alarmSessionTypeId) {
                return $query->where('session_warnings', '>', 0)->where('session_st_id', '!=', $alarmSessionTypeId);
            })
            ->when($this->severityFilter['errors'], function($query) {
                return $query->where('session_errors', '>', 0);
            })
            ->orderByDesc('session_id');


        return $query;
    }

    // THIS historyHeaders are not in active use - in another component
//    public function updateHistoryHeaderData()
//    {
//        $historyHeaders = $this->getSessionsQuery()->orderByDesc('session_start');
//        $this->historyHeaders = $historyHeaders->take($this->currentSessionsAmount)->get();
//        $this->hasMorePages = ( $this->currentSessionsAmount < $historyHeaders->count());
//    }

    public function updateSessionDetails($sessions = [])
    {
        if(!is_array($this->historyVisibility)){
            $this->initHistoryData();
        }

        $openedSession = array_filter($this->historyVisibility);
        if (count($sessions) > count($this->historyVisibility)) {
            $newVisibility = [];
            foreach ($sessions as $value) {
                $newVisibility[$value] = Arr::exists($openedSession, $value);
            }
            $this->historyVisibility = $newVisibility;
        }

//        $openedSessionsDetails = [];
//        $openedSession = array_filter($this->historyVisibility);
//        foreach ($openedSession as $sessionId => $value) {
//            $openedSessionsDetails[$sessionId] = $this->sessionHistoryService->getSessionDetail(
//                $sessionId,
//                ['start_date' => $this->getStartDate($this->dateFilter['dateFromValue'] ?? null), 'end_date' => $this->getEndDate($this->dateFilter['dateToValue'] ?? null)]
//            );
//        }
//
//        $this->openedSessionsDetails = $openedSessionsDetails;
    }


    // todo: to remove - this is given by SessionHistoryService
//    public function getSessionDetail($sessionId)
//    {
//        $this->historyFilter = $this->getHistoryFilter();
//        $startDate = $this->getStartDate($this->dateFilter['dateFromValue'] ?? null);
//        $endDate = $this->getEndDate($this->dateFilter['dateToValue'] ?? null);
//
//
//        // here somethign seems wrong - why not to retrieve session by ID?
//
//        if( $this->historyFilter['calls'] ){
//            return Session::with('session_type', 'alerts', 'sets', 'events')->where('session_id', '=', $sessionId)->calls()->whereBetween('session_start',[$startDate, $endDate])->orderByDesc('session_id')->first();
//        } elseif( $this->historyFilter['carcalls'] ){
//            return Session::with('session_type', 'alerts', 'sets', 'events')->where('session_id', '=', $sessionId)->carcalls()->whereBetween('session_start',[$startDate, $endDate])->orderByDesc('session_id')->first();
//        } elseif( $this->historyFilter['periodicals'] ){
//            return Session::with('session_type', 'alerts', 'sets', 'events')->where('session_id', '=', $sessionId)->alerts()->whereBetween('session_start',[$startDate, $endDate])->orderByDesc('session_id')->first();
//        } elseif( $this->historyFilter['sets'] ){
//            return Session::with('session_type', 'alerts', 'sets', 'sets.setting', 'events')->where('session_id', '=', $sessionId)->sets()->whereBetween('session_start',[$startDate, $endDate])->orderByDesc('session_id')->first();
//        } elseif( $this->historyFilter['revivals'] ){
//            return Session::with('session_type', 'alerts', 'sets', 'sets.setting', 'events')->where('session_id', '=', $sessionId)->revivals()->whereBetween('session_start',[$startDate, $endDate])->orderByDesc('session_id')->first();
//        } else {
//            return Session::with('session_type', 'alerts', 'sets', 'events')->where('session_id', '=', $sessionId)->whereBetween('session_start',[$startDate, $endDate])->orderByDesc('session_id')->first();
//        }
//    }

    public function getHealthStates($session)
    {
        $healthState = [
            'success' => true,
            'warning' => false,
            'error' => false,
            'pending' => false
        ];

         // TO REMOVE after some time
//        foreach($session->sets as $setSession){
//            if(!$setSession->set_success){
//                $healthState['error'] = true;
//                $healthState['success'] = false;
//            }
//        }

        // TO REMOVE after some time
//        foreach($session->alerts as $alertSession){
//            if($alertSession->alert_active && $alertSession->alert_type != null && $alertSession->alert_type->at_type != 'VOICE'){
//                if($alertSession->alert_type->alert_severity->as_type == 'WARNING'){
//                    $healthState['warning'] = true;
//                    $healthState['success'] = false;
//                } elseif($alertSession->alert_type->alert_severity->as_type == 'ERROR') {
//                    $healthState['error'] = true;
//                    $healthState['success'] = false;
//                }
//            }
//        }

        if($session->session_warnings > 0){
            $healthState['warning'] = true;
            $healthState['success'] = false;
        }
        if($session->session_errors > 0 || $session->session_critical > 0){
            $healthState['error'] = true;
            $healthState['success'] = false;
        }
        // TO REMOVE after some time
//        if(!$session->session_complete){
//            $healthState['error'] = true;
//            $healthState['success'] = false;
//        }
        if(!$session->session_complete && $session->session_end == 'pending'){
            $healthState['pending'] = true;
            $healthState['success'] = false;
            if(strtolower($session->session_type->st_type) == 'set' && $this->deviceTasks['set'] == false) {
                $this->deviceTasks['set'] = true;
            } elseif(strtolower($session->session_type->st_type) == 'carcall' && $this->deviceTasks['carcall'] == false) {
                $this->deviceTasks['carcall'] = true;
            } elseif(strtolower($session->session_type->st_type) == 'revival' && $this->deviceTasks['revival'] == false) {
                $this->deviceTasks['revival'] = true;
            }
        } else {
            if($this->deviceTasks['carcall'] == true){ $this->deviceTasks['carcall'] = false;}
            if($this->deviceTasks['set'] == true){ $this->deviceTasks['set'] = false;}
            if($this->deviceTasks['revival'] == true){ $this->deviceTasks['revival'] = false;}
        }
        return $healthState;
    }

    public function getTestSession($sessionId)
    {
        if(!array_key_exists($sessionId, $this->historyDetails)){
            $sessionDetail = Session::with('session_type:st_id,st_type', 'alerts:alert_id,alert_session_id,alert_at_id,alert_active,alert_timestamp', 'alerts.alert_type:at_id,at_as_id,at_type', 'alerts.alert_type.alert_severity:as_id,as_type', 'sets:set_id,set_session_id,set_setting_id,set_value,set_success', 'events:event_id,event_session_id,event_et_id,event_es_id,event_value,event_timestamp','events.event_type:et_id,et_type', 'events.event_severity:es_id,es_type')->where('session_id', '=', $sessionId)->first();
            if($sessionDetail->session_ref_id != null){
                $relatedSessionDetail = Session::with('session_type:st_id,st_type', 'alerts:alert_id,alert_session_id,alert_at_id,alert_active,alert_timestamp', 'sets:set_id,set_session_id,set_setting_id,set_value,set_success', 'events:event_id,event_session_id,event_et_id,event_es_id,event_value,event_timestamp','events.event_type:et_id,et_type', 'events.event_severity:es_id,es_type')->where('session_id', '=', $sessionDetail->session_ref_id)->first()->toArray();
            } else {
                $relatedSessionDetail = null;
            }
            $this->historyDetails[$sessionId]['data'] = $sessionDetail->toArray();
            $this->historyDetails[$sessionId]['related'] = $relatedSessionDetail;
            // $this->historyDetails[$sessionId]['data'] = Session::with('session_type', 'alerts', 'sets', 'events')->where('session_id', '=', $sessionId)->first()->toArray();
            $this->historyDetails[$sessionId]['visibility'] = 1;
        } else {
            $this->historyDetails[$sessionId]['visibility'] = abs($this->historyDetails[$sessionId]['visibility'] - 1);
        }
    }

    public function updateLatestSessionHistoryVisibility()
    {
        if($this->monitorActiveTask){
            do {
                // throttle
                usleep(100000); // 0.1 sec
                $latestSessionId = $this->getLatestSessionId();
            } while (array_key_last($this->historyVisibility) == $latestSessionId);

            $this->historyVisibility = Arr::add($this->historyVisibility, $latestSessionId, true);
            $this->monitorActiveTask = false;
        } else {

            $latestSessionId = $this->getLatestSessionId();
            if ($latestSessionId && array_key_last($this->historyVisibility) != $latestSessionId) {
                $this->historyVisibility = Arr::add($this->historyVisibility, $latestSessionId, false);
            }
        }
    }

    private function getLatestSessionId(): ?int
    {
        if ($this->context === 'all' && $this->deviceSite) {
            return Session::getLatestDeviceSiteOrDevicesSession($this->deviceSite->ds_id, $this->siteDevicesIds)?->session_id;
        } elseif ($this->context === 'only_site' && $this->deviceSite) {
            return Session::getLatestOnlyDeviceSiteSession($this->deviceSite->ds_id)?->session_id;
        } else {
            return Session::getLatestDeviceSession($this->device->device_id)?->session_id;
        }
    }

    /** @deprecated  */
    public function showRelatedInfo($session_ref_id, $session_timestamp)
    {
        $this->relatedEvents = [];
        $relatedSessions = Session::query()->where('session_ref_id','=',$session_ref_id)->get();
        $date = new DateTime($session_timestamp);
        $current = $date->format('Y-m-d H:i:s');
        $id = strtotime($session_timestamp);
        try {
            foreach ($relatedSessions as $key => $item) {
                $start = (is_string($item->session_start) ? (new DateTime($item->session_start))->format('Y-m-d H:i:s') : $item->session_start->format('Y-m-d H:i:s'));
                $end = (is_string($item->session_end) ? (new DateTime($item->session_end))->format('Y-m-d H:i:s') : $item->session_end->format('Y-m-d H:i:s'));
                if($end >= $current && $start <= $current){
                    $this->relatedEvents[$id] = Event::query()->where('event_session_id','=',$item->session_id)->get();
                }
            }
            $this->showRelatedEvents = true;
        } catch(\Throwable $e) {
            $this->showRelatedEvents = false;
        }
        // WHY THIS IS NEEDED - MAKE EVENT IF NEEDED
//        $this->updateDeviceStats();
    }

    /** @deprecated  */
    public function hideRelatedInfo()
    {
        $this->relatedEvents = [];
        $this->showRelatedEvents = false;

        // WHY THIS IS NEEDED - MAKE EVENT IF NEEDED
//        $this->updateDeviceStats();
    }

    public function loadMore()
    {
        $this->perPage += $this->sessionsPerPage;
        // THIs BELOW IS ALSO RATHER NOT IN ACTIVE USE
        $this->currentSessionsAmount += $this->sessionsPerPage;
        // THIS historyHeaders are not in active use
//        $this->updateHistoryHeaderData(); // this was updateDeviceStats - maybe there was an idea behind it
    }

    public function updatedDateFilter()
    {
        $this->dateFilter = $this->storeFilter('dateFilter', $this->dateFilter);
        $this->historyCache = null;
        Cache::forget($this->getHistoryCacheKey());
    }

    private function getCsvHeader()
    {
        return [
            __('SESSION-ID'),
            __('UUID'),
            __('REF-ID'),
            __('SESSION-TYPE'),
            __('EVENT-TYPE'),
            __('EVENT-VALUE'),
            __('EVENT-SEVERITY'),
            __('EVENT_TIMESTAMP')
        ];
    }

//    public function exportHistory()
//    {
//        try {
//            ini_set('max_execution_time', 600);
//            ini_set('memory_limit', '512M');
//
//            $translations = session('translations');
//            $translations['device'] = $translations[$this->locale]['device']['setting'];
//            $this->deviceTranslations = Arr::dot($translations);
//            $this->alertTranslations = $this->getAlertTranslations($this->locale);
//
//            $progressFile = storage_path('framework/cache/export_history_' . auth()->id() . '.txt');
//            file_put_contents($progressFile, '0');
//
//            return response()->streamDownload(function () use ($progressFile) {
//                $file = fopen('php://output', 'w+');
//                $header = $this->getCsvHeader();
//                fputcsv($file, $header);
//
//                $historyData = $this->getSessionsQuery()->get();
//                $total = count($historyData);
//                $processed = 0;
//
//                foreach ($historyData as $history) {
//                    $rows = $this->generateCsvRow($history);
//                    foreach($rows as $oneLine){
//                        fputcsv($file, $oneLine);
//                    }
//
//                    $processed++;
//                    $progress = round(($processed / $total) * 100);
//                    file_put_contents($progressFile, $progress);
//                }
//
//                file_put_contents($progressFile, '100');
//                fclose($file);
//
//                // Clean up after small delay to ensure last progress is read
//                register_shutdown_function(function() use ($progressFile) {
//                    sleep(2);
//                    if (file_exists($progressFile)) {
//                        unlink($progressFile);
//                    }
//                });
//
//            }, 'session-history-'. ($this->device?->device_equipment ?: $this->device?->device_id ?: $this->deviceSite?->ds_name ?: $this->deviceSite?->ds_id ?: '') .'.csv');
//        } catch (\Exception $e) {
//            $progressFile = storage_path('framework/cache/export_history_' . auth()->id() . '.txt');
//            if (file_exists($progressFile)) {
//                unlink($progressFile);
//            }
//            throw $e;
//        }
//    }

    public function exportHistory()
    {
        try {
            ini_set('max_execution_time', 600);
            ini_set('memory_limit', '512M');

            $translations = session('translations');
            $translations['device'] = $translations[$this->locale]['device']['setting'];
            $this->deviceTranslations = Arr::dot($translations);
            $this->alertTranslations = $this->getAlertTranslations($this->locale);

            $progressFile = storage_path('framework/cache/export_history_' . auth()->id() . '.txt');
            file_put_contents($progressFile, '0');

            $header = $this->getCsvHeader();
            $rows = $this->generateExportRows($progressFile);

            file_put_contents($progressFile, '100');

            // Clean up after delay
            register_shutdown_function(function() use ($progressFile) {
                sleep(2);
                if (file_exists($progressFile)) {
                    unlink($progressFile);
                }
            });

            $fileName = 'session-history-'. ($this->device?->device_equipment ?: $this->device?->device_id ?: $this->deviceSite?->ds_name ?: $this->deviceSite?->ds_id ?: '');

            return $this->exportFormat === 'xlsx'
                ? $this->downloadExcel($rows, $header, $fileName)
                : $this->downloadCsv($rows, $header, $fileName);

        } catch (\Exception $e) {
            $progressFile = storage_path('framework/cache/export_history_' . auth()->id() . '.txt');
            if (file_exists($progressFile)) {
                unlink($progressFile);
            }
            throw $e;
        }
    }

    private function generateExportRows($progressFile)
    {
        $rows = [];
        $historyData = $this->getSessionsQuery()->get();
        $total = count($historyData);
        $processed = 0;

        foreach ($historyData as $history) {
            $rows = array_merge($rows, $this->generateCsvRow($history));

            $processed++;
            $progress = round(($processed / $total) * 100);
            file_put_contents($progressFile, $progress);
        }

        return $rows;
    }

    private function downloadExcel($rows, $header, $fileName)
    {
        return Excel::download(
            new HistoryExport($rows, $header),
            $fileName . '.xlsx'
        );
    }

    private function downloadCsv($rows, $header, $fileName)
    {
        return response()->streamDownload(function() use ($rows, $header) {
            $file = fopen('php://output', 'w+');
            fputcsv($file, $header);

            foreach($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, $fileName . '.csv');
    }

    private function generateCsvRow($history)
    {
        $rows = [];
        $session = $history->toArray();
        $common = [
            $session['session_id'],
            $session['session_uuid'],
            $session['session_ref_id'],
            $session['session_type']['st_type'],
        ];

        if(array_key_exists('alerts',$session) && count($session['alerts']) > 0){
            foreach ($session['alerts'] as $alert) {
                $row = $common;
                array_push(
                    $row,
                    $this->alertTranslations[$alert['alert_type']['at_type']] ?? $alert['alert_type']['at_type'], // alert_type
                    $alert['alert_value'], // event_value
                    $alert['alert_type']['alert_severity']['as_type'], // alert_severity
                    toUserDateTime($alert['alert_timestamp']) // alert_timestamp
                );
                $rows[] = $row;
            }
        }
        if(array_key_exists('sets',$session) && count($session['sets']) > 0){
            foreach ($session['sets'] as $set) {
                $row = $common;
                array_push(
                    $row,
                    data_get($this->deviceTranslations, $set['setting']['setting_key'], $set['setting']['setting_key']), // set_type
                    $set['set_value'], // event_value
                    ($set['set_success'] ? 'SUCCESS' : 'ERROR'), // set_severity
                    toUserDateTime($set['set_timestamp']) // set_timestamp
                );
                $rows[] = $row;
            }
        }

        if(array_key_exists('events',$session) && count($session['events']) > 0){
            foreach ($session['events'] as $event) {
                $row = $common;
                array_push(
                    $row,
                    data_get($this->deviceTranslations, $event['event_type']['et_type'], $event['event_type']['et_type']), // event_type
                    $event['event_value'], // event_value
                    $event['event_severity']['es_type'], // event_severity
                    toUserDateTime($event['event_timestamp']) // event_timestamp
                );
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function downloadRecord(string $recordFile)
    {
        $accountSlug = Account::findOrFail(session('account.id'))?->account_slug;
        $parts = explode('/', $recordFile);

        $pathAccountSlug = $parts[3] ?? null;
        if ($pathAccountSlug !== $accountSlug) {
            \Log::warning('Unauthorized file download attempt', [
                'requested_account' => $pathAccountSlug,
                'session_account'   => $accountSlug,
                'filePath'          => $recordFile,
                'user_id'           => auth()->id(),
            ]);
            abort(403, 'Unauthorized access.');
        }


        if (File::exists($recordFile)) {
            return response()->download($recordFile);
        } else {
            $this->notify('error', __('File not found'));
        }

    }

}