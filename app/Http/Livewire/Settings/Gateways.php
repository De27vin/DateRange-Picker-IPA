<?php
namespace App\Http\Livewire\Settings;

use App\Models\DeviceGatewayType;
use App\Services\PasswordPolicyService;
use App\Traits\TranslationsTrait;
use App\Traits\ValidationTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\DeviceGateway;
use App\Rules\PasswordStrengthRule;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Request;

/**
 * @deprecated
 */
class Gateways extends Component
{
    use WithPerPagePagination;
    use TranslationsTrait;
    use WithSorting;
    use WithBulkActions;
    use WithCachedRows;
    use WithFileUploads;
    use ValidationTrait;

    public $realm           = 'serv24.com';
    public $passwordLength  = 12;
    public $breadcrumb      = ['Settings', 'Gateway'];
    public $showFilters     = false;
    public $showSorting     = false;
    public $errors = [];
    public $filters         = [
        'search' => ''
    ];
    public $featureState;
    public $newMacAddress;
    public $newImei;
//    public $newGatewayType;
//    public $gatewayTypes = [];
    public $gateway_imports;
    public $file;
    public $accountId;
    public $tabs = [
        'enabled' => true,
        'disabled' => false,
        'assigned' => false,
        'unassigned' => false,
    ];
    public $showNewGateway;
    public $showImportGateway;
    public $fieldTanslations;
    public $isAdmin = null;
    public $isSite = null;

    public $canLoadMore = false;

    protected $listeners = [
        'confirmDeleted' => '$refresh',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);

//        DB::connection()->enableQueryLog();
    }

//    public function __destruct()
//    {
//        $queries = DB::getQueryLog();
//        $totalTime = array_sum(array_column($queries, 'time'));
//        dd($totalTime, $queries);
//    }

    public function mount()
    {
        $this->showNewGateway   = false;
        $this->showImportGateway   = false;
        $this->accountId = session('account.id');
        $this->isAdmin = Auth::user()->isAdmin;
        $this->isSite = Auth::user()->isSite;
        $this->featureState = true;
        $this->fieldTanslations = $this->getFieldTranslations();
//        $this->gatewayTypes = $this->getGatewayTypesForAdding();

        $query = Request::query('search', null);
        if ($query) {
            $this->filters['search'] = explode(',', $query)[1];
            $this->tabs['enabled'] = false;
            $this->tabs['assigned'] = true;
        }
    }

    public function rules()
    {
        return [
            'editedPasswordField' => ['required', new PasswordStrengthRule($this->accountId)],
        ];
    }

    public function render()
    {
        $this->canLoadMore = match (true) {
            $this->tabs['enabled'] => $this->rowsEnabled->hasMorePages(),
            $this->tabs['disabled'] => $this->rowsDisabled->hasMorePages(),
            $this->tabs['assigned'] => $this->rowsAssigned->hasMorePages(),
            $this->tabs['unassigned'] => $this->rowsUnassigned->hasMorePages(),
            default => false,
        };

        if($this->featureState){
            return view('livewire.settings.gateways', [
                'gateways_enabled' => $this->rowsEnabled,
                'gateways_disabled' => $this->rowsDisabled,
                'gateways_assigned' => $this->rowsAssigned,
                'gateways_unassigned' => $this->rowsUnassigned,
                'fieldTranslations' => $this->fieldTanslations,
            ]);
        } else {
            return view('livewire.settings.gateways', [
                'gateways_enabled' => null,
                'gateways_disabled' => null
            ]);

        }
    }

    public function showNewGatewayForm()
    {
        $this->showNewGateway = true;
    }

    public function hideNewGatewayForm()
    {
        $this->showNewGateway = false;
    }

    public function showImportGatewayForm()
    {
        $this->showImportGateway = true;
    }

    public function hideImportGatewayForm()
    {
        $this->showImportGateway = false;
    }

    public function setTab($activeTab)
    {
        $this->tabs = [
            'enabled' => $activeTab === 'enabled',
            'disabled' => $activeTab === 'disabled',
            'assigned' => $activeTab === 'assigned',
            'unassigned' => $activeTab === 'unassigned',
        ];
    }

    public function getRowsEnabledProperty()
    {
        return $this->applyPagination($this->getGatewayQuery(DeviceGateway::query())->forAccount()->enabled());
    }

    public function getRowsDisabledProperty()
    {
        return $this->applyPagination($this->getGatewayQuery(DeviceGateway::query())->forAccount()->disabled());
    }

    public function getRowsAssignedProperty()
    {
        return $this->applyPagination($this->getGatewayQuery(DeviceGateway::query())->forAccount()->assigned());
    }

    public function getRowsUnassignedProperty()
    {
        return $this->applyPagination($this->getGatewayQuery(DeviceGateway::query())->forAccount()->unassigned());
    }

    public function getGatewayQuery($rawQuery)
    {
        return $rawQuery
            ->with([
                'device.device_site',
                'device.device_comments',
                'device.device_alerts',
                'device.device_site.numbers',
                'device.gateway',
                'device.device_comments',
                'device.device_alerts',
                'device.module',
                'device.module.module_type'
            ])
            ->when($this->filters['search'], function($query, $search){
                $search = trim($search);
                $search = preg_replace('/[ :\-]/', '', $search);
                $search = strtolower($search);
                $query = $query->where('dg_mac', 'like', '%'.$search.'%');
                $query = $query->orWhere('dg_imei', 'like', '%'.$search.'%');
//                $query = $query->orWhereHas('type', function ($query) use ($search) {
//                    $query->where('dgt_type', 'like', '%'.$search.'%');
//                });
//                $query = $query->orWhereHas('device_site', function ($query) use ($search) {
//                    $query->where('ds_name', 'like', '%'.$search.'%');
//                });
//                $query = $query->orWhereHas('device_site.numbers', function ($query) use ($search) {
//                    $query->where('number_value', 'like', '%'.$search.'%');
//                });
                return $query;
            })
            ->orderBy('dg_mac');
    }

    public function saveGateways()
    {
        $this->validateNewGatewayRequiredFields();

        $macAddress = trim($this->newMacAddress);
        $macAddress = preg_replace('/[ :\-]/', '', $macAddress);
        $macAddress = strtolower($macAddress);
        $macAddress = $macAddress ?: null;

        $imei = trim($this->newImei);
        $imei = preg_replace("/[^0-9]/", "", $imei);
        $imei = $imei ?: null;

        if (!empty($macErrors = $this->validateMacAddress($macAddress))) {
            $this->addError('newMacAddress', $macErrors[0]);
        }
        if (!empty($imeiErrors = $this->validateImei($imei))) {
            $this->addError('newImei', $imeiErrors[0]);
        }
        if ($macErrors || $imeiErrors) {
            return;
        }

        try {
            $this->saveNewGateway($macAddress, $imei);
        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            $this->notify('error', trans('Error - gateway not saved'));
            return;
        }

        $this->notify('success', trans('New gateway saved'));
        $this->newMacAddress = null;
        $this->newImei = null;
    }

    private function saveNewGateway($gatewayMacAddress = null, $imei = null)
    {
        $gateway = new DeviceGateway();
        $data = [
            'mac' => $gatewayMacAddress,
            'imei' => $imei,
            'account_id' => session('account.id'),
        ];
        $gateway->addData($data);
    }

    private function validateNewGatewayRequiredFields()
    {
        $this->validate(
            [
                'newMacAddress' => 'sometimes|nullable|required_without:newImei',
                'newImei' => 'sometimes|nullable|required_without:newMacAddress',
            ],
            [
                'newMacAddress.required_without' => __('At least one field is required'),
                'newImei.required_without' => __('At least one field is required'),
            ],
        );
    }

//    private function getGatewayTypesForAdding(): array
//    {
//        return DeviceGatewayType::where('dgt_type', '<>', 'GSR')->get()->pluck('dgt_desc', 'dgt_id')->toArray();
//    }
}
