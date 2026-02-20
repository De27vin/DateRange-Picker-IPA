<?php
namespace App\Traits;

use Carbon\Carbon;

trait DeviceSiteTrait
{

    public $itemDisplayState;
    public $deviceFieldSettings;

    /**
     * configure display states of device-site box items
     * @param boolean $site             
     * @param boolean $alerts           
     * @param boolean $core             
     * @param boolean $custom           
     * @param boolean $address          
     * @param boolean $labels           
     * @param boolean $comment          
     * @param boolean $states           
     * @param boolean $linkSiteDetail   
     * @param boolean $linkDeviceDetails
     * @param boolean $siteEdit         
     * @param boolean $deviceEdit       
     * @return array
     * **/
    
    public function getItemDisplayState(
        $site              = false,
        $alerts            = false,
        $core              = false,
        $custom            = false,
        $address           = false,
        $labels            = false,
        $comment           = false,
        $states            = false,
        $linkSiteDetail    = false,
        $linkDeviceDetail  = false,
        $siteEdit          = false,
        $deviceEdit        = false,
        $showAllDevices    = false,
        $withDeviceForm    = false,
    )
    {
        return [
            'site'              => $site,
            'alerts'            => $alerts,
            'core'              => $core,
            'custom'            => $custom,
            'address'           => $address,
            'labels'            => $labels,
            'comment'           => $comment,
            'states'            => $states,
            'linkSiteDetail'    => $linkSiteDetail,
            'linkDeviceDetail'  => $linkDeviceDetail,
            'siteEdit'          => $siteEdit,
            'deviceEdit'        => $deviceEdit,
            'showAllDevices'    => $showAllDevices,
            'withDeviceForm'    => $withDeviceForm,
        ];
    }

    /**
     * prepares a collection of deviceSites for device-boxes
     * @param collection $deviceSiteCollection
     * @return array
     * 
     ***/
    public function mapDeviceSiteCollection($deviceSiteCollection):array
    {
        // dd($deviceSiteCollection);
        $deviceSteArray = $deviceSiteCollection->mapToGroups(function($item,$key){
            // dd($item);
            $site['numbers'] = [];
            $numbers = [];
            $site = [
                'ds_id' => $item->ds_id,
                'ds_name' => $item->ds_name,
                'module' => [
                    'module_id' => $item->module->module_id,
                    'name' => $item->module->module_name,
                ]
            ];
            if(!is_null($item->device_gateway)){
                $gateway = [
                    'gateway_id' => $item->device_gateway->dg_id,
                    'macaddress' => $item->device_gateway->dg_mac,
                    'name' => $item->device_gateway->device_gateway_type->dgt_desc
                ];
                if(!is_null($item->device_gateway->numbers)){
                    $site['numbers'] = $item->device_gateway->numbers->mapWithKeys(function($item2,$key2){
                        return [$item2->number_type->nt_type => $item2->number_value];
                    })->toArray();
                } elseif(!is_null($item->numbers)){
                    $site['numbers'] = $item->numbers->mapWithKeys(function($item2,$key2){
                        return [$item2->number_type->nt_type => $item2->number_value];
                    })->toArray();
                }
            } else {
                if(!is_null($item->numbers)){
                    $site['numbers'] = $item->numbers->mapWithKeys(function($item2,$key2){
                        return [$item2->number_type->nt_type => $item2->number_value];
                    })->toArray();
                }
                $gateway = [];
            }
            $site['gateway'] = $gateway;
            $devices = [];
            foreach ($item->devices as $device) {
                $address = '';
                $latestComment = [];
                $labels = [];
                $alerts = [
                    'WARNING' => [],
                    'ERROR' => []
                ];
                foreach ($device->device_alerts as $alert) {
                    $alerts[$alert->alert_type->alert_severity->as_type][] = $alert->alert_type->at_type;
                }
                foreach ($device->device_labels as $label) {
                    $labels[] = $label->dl_name;
                }
                if(!is_null($device->address)){
                    $address = $device->address->in_one_line;
                }
                if(!is_null($device->latest_comment)){
                    $latestComment = [
                        'text' => $device->latest_comment->dc_text,
                        'author' => $device->latest_comment->author,
                        'link' => $device->latest_comment->dc_link
                    ];
                }
                $devices[$device->device_id] = [
                    'device_id'          => $device->device_id,
                    'device_equipment'   => $device->device_equipment,
                    'device_identity'    => $device->device_identity,
                    'device_setidentity' => $device->device_setidentity,
                    'device_module'      => $device->device_module,
                    'device_setmodule'   => $device->device_setmodule,
                    'device_pin'         => $device->device_pin,
                    'device_setpin'      => $device->device_setpin,
//                    'device_tech'     => $device->device_tech,
//                    'device_custom'     => $device->device_custom,
//                    'device_custom3'     => $device->device_custom3,
//                    'device_custom4'     => $device->device_custom4,
                    'device_link'        => $device->device_link,
                    'device_enabled'     => $device->device_enabled,
                    'device_deleted'     => $device->device_deleted,
                    'alerts'             => $alerts,
                    'address'            => $address,
                    'latest_comment'     => $latestComment,
                    'labels'             => $labels,
                    'states'             => json_decode(json_encode($device->states), true)
                ];
                if($devices[$device->device_id]['states']['device_overdue']){
                    $devices[$device->device_id]['states']['overdue_since'] = Carbon::parse(toUserDateTime($devices[$device->device_id]['states']['device_lastreported']))->diffForHumans(Carbon::now());
                    $devices[$device->device_id]['states']['overdue_days'] = Carbon::parse(toUserDateTime($devices[$device->device_id]['states']['device_lastreported']))->diffInDays(Carbon::now());
                    $devices[$device->device_id]['overdue_seconds'] = Carbon::parse(toUserDateTime($devices[$device->device_id]['states']['device_lastreported']))->diffInSeconds(Carbon::now());
                } else {
                    $devices[$device->device_id]['states']['overdue_since'] = 0;
                    $devices[$device->device_id]['states']['overdue_days'] = 0;
                    $devices[$device->device_id]['overdue_seconds'] = 0;
                }
            }
            $temp = [
                'site' => $site,
                'devices' => $devices
            ];
            return [$item->ds_id => $temp];
        })->collapse()->toArray();
        return $deviceSteArray;
    }

    public function getEmptyDevice():array
    {
        return [
            'equipment'   => null,
            'identity'    => null,
            'module'      => null,
            'pin'         => null,
//            'tech'     => null,
//            'custom'     => null,
//            'custom3'     => null,
//            'custom4'     => null,
            'link' => null,
            'address'       => [
                'address'  => null,
                'location' => [
                    'location_postcode'   => null,
                    'location_value'      => null,
                    'location_country_id' => null,
                ]
            ],
            'comments' => [
                'text'   => null,
                'author' => null,
                'link'   => null
            ],
            'labels' => [],
        ];
    }


}