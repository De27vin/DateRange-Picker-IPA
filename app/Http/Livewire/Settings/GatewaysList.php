<?php

namespace App\Http\Livewire\Settings;

use App\Helpers\GroupCache;
use App\Models\DeviceGateway;
use App\Rules\PasswordStrengthRule;
use App\Traits\FreeswitchApiTrait;
use App\Traits\PasswordPolicyTrait;
use App\Traits\TranslationsTrait;
use App\Traits\ValidationTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Http\Livewire\DataTable\WithSorting;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithPerPagePagination;

class GatewaysList extends Component
{
    use WithPerPagePagination,
        TranslationsTrait,
        WithSorting,
        WithBulkActions,
        WithCachedRows,
        WithFileUploads,
        ValidationTrait,
        FreeswitchApiTrait,
        PasswordPolicyTrait;

    public $realm = 'serv24.com';
    public $accountId;
    public $isAdmin = null;
    public $isSite = null;
    public $filters = ['search' => ''];
    public $tabs = [
        'enabled'   => true,
        'disabled'  => false,
        'assigned'  => false,
        'unassigned'=> false,
    ];

    // Gateway editing properties
    public $editedGatewayIndex = null;
    public $editedGatewayField = null;
    public $editedPasswordField = null;

    // New gateway properties
    public $showNewGateway = false;
    public $newMacAddress;
    public $newImei;

    public $canLoadMore = false;

    protected $listeners = [
        'confirmDeleted' => '$refresh',
        'removeGateway'  => 'deleteGateway',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public function mount()
    {
        $this->accountId = session('account.id');
        $this->isAdmin   = Auth::user()->isAdmin;
        $this->isSite    = Auth::user()->isSite;

        $query = request('search');
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
            $this->tabs['enabled']    => $this->rowsEnabled->hasMorePages(),
            $this->tabs['disabled']   => $this->rowsDisabled->hasMorePages(),
            $this->tabs['assigned']   => $this->rowsAssigned->hasMorePages(),
            $this->tabs['unassigned'] => $this->rowsUnassigned->hasMorePages(),
            default                  => false,
        };

        return view('livewire.settings.gateways.list', [
            'gateways_enabled'  => $this->rowsEnabled,
            'gateways_disabled' => $this->rowsDisabled,
            'gateways_assigned' => $this->rowsAssigned,
            'gateways_unassigned'=> $this->rowsUnassigned,
        ]);
    }

    // Query properties with caching (TTL set to 600 seconds)
    public function getRowsEnabledProperty()
    {
        $cacheKey = __CLASS__.__METHOD__.$this->accountId.'_'.$this->perPage.'_'.md5($this->filters['search']);
        return GroupCache::remember('gateways', $cacheKey, 60, function() {
            return $this->applyPagination(
                $this->getGatewayQuery(DeviceGateway::query())->forAccount()->enabled()
            );
        });
    }

    public function getRowsDisabledProperty()
    {
        $cacheKey = __CLASS__.__METHOD__.$this->accountId.'_'.$this->perPage.'_'.md5($this->filters['search']);
        return GroupCache::remember('gateways', $cacheKey, 60, function() {
            return $this->applyPagination(
                $this->getGatewayQuery(DeviceGateway::query())->forAccount()->disabled()
            );
        });
    }

    public function getRowsAssignedProperty()
    {
        $cacheKey = __CLASS__.__METHOD__.$this->accountId.'_'.$this->perPage.'_'.md5($this->filters['search']);
        return GroupCache::remember('gateways', $cacheKey, 60, function() {
            return $this->applyPagination(
                $this->getGatewayQuery(DeviceGateway::query())->forAccount()->assigned()
            );
        });
    }

    public function getRowsUnassignedProperty()
    {
        $cacheKey = __CLASS__.__METHOD__.$this->accountId.'_'.$this->perPage.'_'.md5($this->filters['search']);
        return GroupCache::remember('gateways', $cacheKey, 60, function() {
            return $this->applyPagination(
                $this->getGatewayQuery(DeviceGateway::query())->forAccount()->unassigned()
            );
        });
    }

    protected function getGatewayQuery($query)
    {
        return $query
            ->with([
                'device.device_site',
                'device.device_comments',
                'device.device_alerts',
                'device.device_site.numbers',
                'device.gateway',
                'device.module',
                'device.module.module_type'
            ])
            ->when($this->filters['search'], function($query, $search) {
                $search = strtolower(preg_replace('/[ :\-]/', '', trim($search)));
                return $query->where(function($q) use ($search) {
                    $q->where('dg_mac', 'like', '%'.$search.'%')
                      ->orWhere('dg_imei', 'like', '%'.$search.'%')
                      ->orWhereHas('device.device_site', function($q) use ($search) {
                          $q->where('ds_name', 'like', '%'.$search.'%');
                      })
                      ->orWhereHas('device', function($q) use ($search) {
                          $q->where('device_equipment', 'like', '%'.$search.'%');
                      })
                      ->orWhereHas('device.module', function($q) use ($search) {
                          $q->where('module_desc', 'like', '%'.$search.'%');
                      });
                });
            })
            ->orderBy('dg_mac');
    }

    // Tab management
    public function setTab($activeTab)
    {
        $this->tabs = array_fill_keys(['enabled', 'disabled', 'assigned', 'unassigned'], false);
        $this->tabs[$activeTab] = true;
    }

    // Gateway management
    public function toggleGatewayState($id)
    {
        $gateway = DeviceGateway::findOrFail($id);
        $gateway->dg_enabled = !$gateway->dg_enabled;
        $gateway->save();
        // Flush all gateway caches after update
        GroupCache::forgetGroup('gateways');

        $this->notify('info', $gateway->dg_enabled ?
            trans('Gateway enabled') :
            trans('Gateway disabled')
        );
    }

    public function editGateway($gatewayId)
    {
        $this->editedGatewayIndex = $gatewayId;
        try {
            $gateway = DeviceGateway::query()->where('dg_id', $gatewayId)->first();
            $this->editedGatewayField = $gateway->dg_mac ?: null;
            $this->editedPasswordField = $gateway->dg_sippwd ?: null;
            $this->gateway = $gateway;
        } catch (\Throwable $e) {
            $this->notify('warning', trans('Gateway not found'));
            $this->editedGatewayIndex = null;
        }
    }

    public function editGatewayCancel()
    {
        $this->editedGatewayIndex = null;
    }

    public function editGatewaySave($id, string $mac, string $password)
    {
        if (!$this->validate()) {
            return false;
        }
        $editedGatewayLogin = trim($mac);
        $editedGatewayPassword = trim($password);
        if ($mac == null) {
            $this->notify('warning', trans('Gateway should not be empty'));
        } else {
            if ($this->gatewayAlreadyExists($id, $mac)) {
                $this->notify('warning', trans('Gateway already exists'));
            } else {
                $gateway = DeviceGateway::with('device')->where('dg_id', $id)->first();
                if ($gateway != null) {
                    $gateway->dg_mac     = $editedGatewayLogin ?: null;
                    $gateway->dg_sippwd  = $editedGatewayPassword;
                    $gateway->dg_siphash = $this->realm;
                    $gateway->save();
                    $this->gateway = $gateway->refresh();
                    $this->notify('success', trans('Gateway data updated'));
                    $this->makeFsReloadGateway($id);
                    // Flush cache for gateways
                    GroupCache::forgetGroup('gateways');
                } else {
                    $this->notify('warning', trans('Gateway not found'));
                }
                $this->editedGatewayIndex = null;
                $this->editedGatewayField = null;
                return true;
            }
        }
    }

    // New gateway management
    public function toggleNewGatewayForm()
    {
        $this->showNewGateway = !$this->showNewGateway;
        if (!$this->showNewGateway) {
            $this->newMacAddress = null;
            $this->newImei = null;
        }
    }

    public function loadMore()
    {
        $this->perPage += 50;
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
        // Flush gateways cache after inserting new record
        GroupCache::forgetGroup('gateways');
    }

    private function saveNewGateway($gatewayMacAddress = null, $imei = null)
    {
        $gateway = new DeviceGateway();
        $data = [
            'mac'        => $gatewayMacAddress,
            'imei'       => $imei,
            'account_id' => session('account.id'),
        ];
        $gateway->addData($data);
    }

    private function validateNewGatewayRequiredFields()
    {
        $this->validate(
            [
                'newMacAddress' => 'sometimes|nullable|required_without:newImei',
                'newImei'       => 'sometimes|nullable|required_without:newMacAddress',
            ],
            [
                'newMacAddress.required_without' => 'At least one field is required',
                'newImei.required_without'       => 'At least one field is required',
            ]
        );
    }

    // Helper methods
    protected function formatMacAddress(?string $mac): ?string
    {
        if (!$mac) return null;
        return strtolower(preg_replace('/[ :\-]/', '', trim($mac)));
    }

    protected function formatImei(?string $imei): ?string
    {
        if (!$imei) return null;
        return preg_replace("/[^0-9]/", "", trim($imei));
    }

    protected function gatewayMacExists($currentId, $mac): bool
    {
        return DeviceGateway::where('dg_mac', $mac)
            ->where('dg_id', '!=', $currentId)
            ->where('dg_account_id', $this->accountId)
            ->exists();
    }

    public function deleteGateway($id)
    {
        if (!$this->isSite) {
            $this->notify('error', trans('Unauthorized to delete gateway'));
            return;
        }

        $gateway = DeviceGateway::findOrFail($id);
        if ($gateway->device) {
            $this->notify('error', trans('Cannot delete gateway with device attached'));
            return;
        }

        $success = false;
        DB::beginTransaction();
        try {
            $gateway->delete();
            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->notify('error', trans('Error on gateway delete'));
        }

        if ($success) {
            $this->notify('success', trans('Gateway deleted'));
            $this->makeFsReloadGateway($id);
            $this->dispatchBrowserEvent('removeListItem', ['item' => 'gateway' . $id]);
            // Flush gateways cache after deletion
            GroupCache::forgetGroup('gateways');
        }
    }

    public function refreshPassword($id)
    {
        $gateway = DeviceGateway::findOrFail($id);
        $gateway->dg_sippwd  = $this->generatePassword($this->accountId);
        $gateway->dg_siphash = $this->realm;
        $gateway->save();

        $this->notify('success', trans('Password refreshed'));
        $this->makeFsReloadGateway($id);
        // Flush gateways cache after updating password
        GroupCache::forgetGroup('gateways');
    }

    public function makeFsReloadGateway($id)
    {
        if ($result = $this->fsMake('ucp del gw ' . $id, false, true)) {
            $this->notify('success', __('ucp del gw command processed'));
        } else {
            $this->notify('error', __('ucp del gw command failed'));
        }
    }

    public function gatewayAlreadyExists($dg_id, $dg_mac)
    {
        $gateway = DeviceGateway::query()->where('dg_id', '=', $dg_id)->first();
        if ($gateway->dg_mac != $dg_mac) {
            if (DeviceGateway::query()
                    ->where('dg_mac', '=', $dg_mac)
                    ->where('dg_mac', '!=', $dg_mac)
                    ->where('dg_account_id', '=', $gateway->dg_account_id)
                    ->first() != null) {
                return true;
            }
        }
        return false;
    }
}
