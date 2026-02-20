<?php
namespace App\Http\Livewire\Settings;

use App\Helpers\Ucp;
use App\Models\DeviceSite;
use App\Traits\FreeswitchApiTrait;
use Livewire\Component;
use App\Models\DeviceGateway;
use App\Rules\PasswordStrengthRule;
use Illuminate\Support\Facades\DB;
use App\Traits\PasswordPolicyTrait;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated
 */
class GatewayItem extends Component
{
    use PasswordPolicyTrait;
    use FreeswitchApiTrait;

    public $realm               = 'serv24.com';
    public $passwordLength      = 12;
    public $accountId;
    public $isAdmin             = null;
    public $isSite              = null;
    public $editedGatewayIndex  = null;
    public $editedGatewayField  = null;
    public $editedPasswordField = null;
//    public $editedSiteAssigned = [];
//    public $assignableSites = [];
    public $fieldTranslations;
    public $gateway;

    public $selectedField = 'editedSiteAssigned';

    public function mount($gateway = null)
    {
        $this->gateway = $gateway;
        $this->accountId = session('account.id');
        $this->isAdmin = Auth::user()->isAdmin;
        $this->isSite = Auth::user()->isSite;
//        $this->assignableSites = [['label'=>'', 'value'=>'']];
    }

    public function rules()
    {
        return [
            'editedPasswordField' => ['required', new PasswordStrengthRule($this->accountId)],
        ];
    }

    public function render()
    {
        return view('livewire.settings.gateway-item',[
            'gateway' => $this->gateway
        ]);
    }

    public function toggleGatewayState($id)
    {
        $affectedGateway = DeviceGateway::where('dg_id','=',$id)->first();
        if($affectedGateway->dg_enabled == null){
            $affected = DB::table('device_gateways')
                ->where('dg_id','=',$affectedGateway->dg_id)
                ->update([
                    'dg_enabled' => 1
                ]);
            $this->notify('info',trans('Gateway enabled'));
        } else {
            $affected = DB::table('device_gateways')
                ->where('dg_id','=',$affectedGateway->dg_id)
                ->update([
                    'dg_enabled' => 0
                ]);
            $this->notify('info',trans('Gateway disabled'));
        }
        $this->gateway = $affectedGateway = DeviceGateway::where('dg_id','=',$id)->first();
    }

    public function deleteGateway($id)
    {
        $success = false;
        DB::beginTransaction();
        try {
            DeviceGateway::find($id)->delete();
            DB::commit();
            $success = true;
        } catch (\Throwable $e) {
            DB::rollback();
            $this->notify('error', trans('Error on gateway delete'));
        }

        if ($success) {
            $this->notify('success', trans('Gateway deleted'));
            $this->makeFsDeleteGateway($id);
            $this->dispatchBrowserEvent('removeListItem', ['item' => 'gateway'.$id]);
            $this->emitUp('confirmDeleted');
        }
    }

    public function editGateway($gatewayId)
    {
        $this->editedGatewayIndex = $gatewayId;
//        $this->editedSiteAssigned = $this->getAssignedSite();
//        $emptySiteAssignedOption = ['label' => __('No site'), 'value' => ''];
//        $this->assignableSites = array_merge([$emptySiteAssignedOption], [$this->editedSiteAssigned], $this->getUnassignedSites());
        try{
            $gateway = DeviceGateway::query()->where('dg_id', $gatewayId)->first();
            $this->editedGatewayField = $gateway->dg_mac ?: null;
            $this->editedPasswordField = $gateway->dg_sippwd ?: null;
            $this->gateway = $gateway;
        } catch (\Throwable $e) {
            $this->notify('warning',trans('Gateway not found'));
            $this->editedGatewayIndex = null;
        }
    }

    public function editGatewayCancel()
    {
        $this->editedGatewayIndex = null;
    }

    public function editGatewaySave($id, String $value, String $password)
    {
        if(!$this->validate()){
            return false;
        }
        $editedGatewayLogin = trim($value);
        $editedGatewayPassword = trim($password);
        if($value = null){
            $this->notify('warning',trans('Gateway should not be empty'));
        } else {
            if($this->gatewayAlreadyExists($id, $value)){
                $this->notify('warning',trans('Gateway already exists'));
            } else {
                $gateway = DeviceGateway::with('device')->where('dg_id', $id)->first();
                if($gateway != null){

//                    $this->updateSiteAssignment($gateway);
                    $gateway->dg_mac = $editedGatewayLogin ?: null;
                    $gateway->dg_sippwd = $editedGatewayPassword;
                    $gateway->dg_siphash = $this->realm;
                    $gateway->save();
                    $this->gateway = $gateway->refresh();
                    $this->notify('success',trans('Gateway data updated'));
                    $this->makeFsReloadGateway($id);
                } else {
                    $this->notify('warning',trans('Gateway not found'));
                }
                $this->editedGatewayIndex = null;
                $this->editedGatewayField = null;
                return true;
            }
        }
    }

    // search constraints inside are rather strange - it needs to be tested and maybe improved or removed
    public function gatewayAlreadyExists($dg_id, $dg_mac)
    {
        $gateway = DeviceGateway::query()->where('dg_id','=',$dg_id)->first();
        if($gateway->dg_mac != $dg_mac){
            if(DeviceGateway::query()
                    ->where('dg_mac','=',$dg_mac)
                    ->where('dg_mac','!=',$dg_mac)
                    ->where('dg_account_id','=',$gateway->dg_account_id)
                    ->first() != null){
                return true;
            }
        }
        return false;
    }

    public function refreshPassword($id)
    {
        $current = DeviceGateway::where('dg_id', $id)->first();
        $current->dg_sippwd = $this->generatePassword($this->accountId);
        $current->dg_siphash = $this->realm;
        $current->save();
        $this->gateway = $current;
        $this->notify('success', trans('Password refreshed'));
        $this->makeFsReloadGateway($id);
    }


    private function getUnassignedSites(): array
    {
        return DeviceSite::forAccount()->where('ds_dg_id', null)->get()->pluck('ds_id', 'ds_name')->map(function ($value, $label) {
            return ['label' => (string) $label, 'value' => (string) $value];
        })->values()->toArray();
    }

    private function getAssignedSite(): ?array
    {
        $siteName = (string) $this->gateway->device_site?->ds_name;
        return [
            'label' => $siteName ? $siteName .' ('.__('current').')' : '',
            'value' => (string) $this->gateway->device_site?->ds_id,
        ];
    }

    private function updateSiteAssignment(DeviceGateway $gateway): void
    {
        $siteId = $this->editedSiteAssigned['value'];
        if ($siteId != $gateway->device_site?->ds_id) {
            if (empty($siteId)) {
                //detach site
                $gateway->device_site->ds_dg_id = null;
                foreach ($gateway->numbers as $number) {
                    $number->number_dg_id = null;
                    $number->number_ds_id = $gateway->device_site->ds_id;
                    $number->save();
                }
                $gateway->device_site->save();
            } elseif (empty($gateway->device_site?->ds_id)) {
                // attach site
                $site = DeviceSite::findOrFail($siteId);
                $site->ds_dg_id = $gateway->dg_id;
                foreach ($site->numbers as $number) {
                    $number->number_ds_id = null;
                    $number->number_dg_id = $gateway->dg_id;
                    $number->save();
                }
                $site->save();
            } else {
                // change assignment
                //detach site
                $gateway->device_site->ds_dg_id = null;
                foreach ($gateway->numbers as $number) {
                    $number->number_dg_id = null;
                    $number->number_ds_id = $gateway->device_site->ds_id;
                    $number->save();
                }
                $gateway->device_site->save();
                // attach site
                $site = DeviceSite::findOrFail($siteId);
                $site->ds_dg_id = $gateway->dg_id;
                foreach ($site->numbers as $number) {
                    $number->number_ds_id = null;
                    $number->number_dg_id = $gateway->dg_id;
                    $number->save();
                }
                $site->save();
            }
        }
    }

    public function makeFsReloadGateway($id)
    {
        if($result = $this->fsMake('ucp del gw ' . $id, false, true)) {
            $this->notify('success', __('ucp reload gw command processed'));
        } else {
            $this->notify('error', __('ucp reload gw command failed'));
        }
    }
    public function makeFsDeleteGateway($id)
    {
        if($result = $this->fsMake('ucp del gw ' . $id, false, true)) {
            $this->notify('success', __('ucp del gw command processed'));
        } else {
            $this->notify('error', __('ucp del gw command failed'));
        }
    }
}
