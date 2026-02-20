<?php
namespace App\Services;

use App\Models\Device;
use App\Models\DeviceSite;
use App\Searchable\Search;
use App\Searchable\SearchResultCollection;
use App\Traits\DeviceFormTrait;
use App\Traits\SearchFiltersTrait;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Support\Facades\DB;

class SearchDeviceService
{
//    use DeviceFormTrait;
//    use SearchFiltersTrait;

    /**
     * Build DeviceSite query with filters - returns Query Builder for pagination
     */
    public function buildDeviceSitesQuery(array $filters)
    {
        $searchTabs = $filters['search_tabs'];
        $searchAlerts = array_keys(array_filter($filters['alerts']));
        $searchLabels = array_keys($filters['groups']);
        $searchSelected = $filters['search_selected'];
        $searchPhrase = trim($filters['search']);
        $sortDirection = $filters['sortDirection'];
        $sortField = $filters['sortedby'];

        // Build query with comprehensive eager loading
        $query = DeviceSite::query()
            ->with([
                'devices.module.module_type', 'devices.gateway', 'comments',
                'devices.custom_fields', 'devices.module.funktions', 
                'module', 'module.module_type', 'numbers', 'address', 
                'address.location', 'custom_fields', 'labels'
            ]);

        // Apply alert filters
        if (!empty($searchAlerts)) {
            $query->withAlerts($searchAlerts);
        }

        // Apply label filters
        if (!empty($searchLabels)) {
            $query->withAnyLabels($searchLabels);
        }

        // Apply tab filters
        if (!empty($searchTabs) && !in_array('all', $searchTabs)) {
            $query->where(function ($q) use ($searchTabs) {
                $hasCondition = false;
                
                if (in_array('enabled', $searchTabs)) {
                    $q->whereHas('devices', function ($query) {
                        $query->where('device_enabled', 1);
                    });
                    $hasCondition = true;
                }
                
                if (in_array('disabled', $searchTabs)) {
                    if ($hasCondition) {
                        $q->orWhereHas('devices', function ($query) {
                            $query->where('device_enabled', 0);
                        });
                    } else {
                        $q->whereHas('devices', function ($query) {
                            $query->where('device_enabled', 0);
                        });
                        $hasCondition = true;
                    }
                }
                
                if (in_array('empty', $searchTabs)) {
                    if ($hasCondition) {
                        $q->orWhereDoesntHave('devices');
                    } else {
                        $q->whereDoesntHave('devices');
                    }
                }
            });
        }

        // Apply search phrase filter
        if ($searchPhrase !== '' && $searchPhrase !== null) {
            $searchResults = $this->performOptimizedSearch($searchPhrase, $searchSelected);
            $searchSiteIds = [];
            
            foreach ($searchResults as $result) {
                if (!empty($result->site_id)) {
                    $searchSiteIds[] = $result->site_id;
                }
            }
            
            $searchSiteIds = array_unique($searchSiteIds);
            
            if (!empty($searchSiteIds)) {
                $query->whereIn('ds_id', $searchSiteIds);
            } else {
                // No search results, return empty query
                $query->whereRaw('1 = 0');
            }
        }

        // Apply sorting at query level with fallback
        $validSiteColumns = ['ds_name', 'ds_modified', 'ds_created'];
        if (!in_array($sortField, $validSiteColumns)) {
            $sortField = 'ds_name'; // Default fallback for sites
        }
        
        // Apply sorting with system sites always first (if they match the filters)

//      // DONT REMOVE COMMENTED BELOW SYSTEM SORTING - THIS FUNCTIONALITY MIGHT BE REVERTED - Check if account has system sites (cached for 1 hour) - THIS FUNCTIONALITY MIGHT BE REVERTED -  Check if account has system sites (cached for 1 hour)
//        $accountId = session('account.id');
//        $hasSystemSites = cache()->remember("account_{$accountId}_has_system_sites", 3600, function() {
//            return DeviceSite::system()->exists();
//        });
//
//        if ($hasSystemSites) {
//            $systemQuery = DeviceSite::system()->select('ds_id');
//            $systemSubquery = $systemQuery->toSql();
//            $systemBindings = $systemQuery->getBindings();
//
//            // Add the bindings to our main query
//            $query->orderByRaw("
//                CASE
//                    WHEN ds_id IN ({$systemSubquery})
//                    THEN 0
//                    ELSE 1
//                END,
//                {$sortField} {$sortDirection},
//                ds_id {$sortDirection}
//            ", $systemBindings);
//        } else {
            $query->orderBy($sortField, $sortDirection)->orderBy('ds_id', $sortDirection);
//        }
        

        return $query;
    }

    /**
     * Build Device query with filters - returns Query Builder for pagination
     */
    public function buildDevicesQuery(array $filters, $getGatewayAlertDevices = false, $baseQuery = null, $excludeGateways = false)
    {
        $searchAlerts = array_keys(array_filter($filters['alerts'] ?? []));
        $searchLabels = array_keys($filters['groups'] ?? []);
        $searchSelected = $filters['search_selected'] ?? [];
        $searchPhrase = trim($filters['search'] ?? '');
        $sortDirection = $filters['sortDirection'] ?? 'asc';
        $sortField = $filters['sortedby'] ?? 'device_equipment';

        // Build query with comprehensive eager loading
        $query = $baseQuery ?? Device::query();
        $query->with([
            'module.module_type', 'device_site', 'device_site.address', 
            'device_site.address.location', 'device_site.numbers', 'custom_fields'
        ]);

        // Apply alert filters with gateway expansion logic
        if (!empty($searchAlerts)) {
            if ($getGatewayAlertDevices) {
                // Include devices that either:
                // 1. Have alerts themselves, OR
                // 2. Are from sites where gateways have alerts (for aggregation)
                $query->where(function ($q) use ($searchAlerts) {
                    // Devices with direct alerts
                    $q->whereHas('device_alerts', function($q2) use ($searchAlerts) {
                        $q2->whereHas('alert_type', function ($q3) use ($searchAlerts) {
                            $q3->whereIn('alert_types.at_type', $searchAlerts);
                        });
                    })
                    // OR devices from sites that have gateway devices with alerts
                    ->orWhereIn('device_ds_id', function($subQuery) use ($searchAlerts) {
                        $subQuery->select('device_ds_id')
                            ->from('devices as gateway_devices')
                            ->join('modules', 'gateway_devices.device_module_id', '=', 'modules.module_id')
                            ->join('module_types', 'modules.module_mt_id', '=', 'module_types.mt_id')
                            ->whereExists(function($alertQuery) use ($searchAlerts) {
                                $alertQuery->select(DB::raw(1))
                                    ->from('device_alerts')
                                    ->join('alert_types', 'device_alerts.da_at_id', '=', 'alert_types.at_id')
                                    ->whereColumn('device_alerts.da_device_id', 'gateway_devices.device_id')
                                    ->whereIn('alert_types.at_type', $searchAlerts);
                            })
                            ->where('module_types.mt_type', 'GATEWAY')
                            ->where('gateway_devices.device_deleted', '0000-00-00 00:00:00');
                    });
                });
            } else {
                // Standard alert filtering without gateway expansion
                $query->withAlerts($searchAlerts);
            }
        }

        // Apply label filters
        if (!empty($searchLabels)) {
            $query->withAnyLabels($searchLabels);
        }

        // Apply gateway exclusion filter (after alert expansion logic)
        if ($excludeGateways) {
            $query->whereHas('module.module_type', function ($q) {
                $q->where('mt_type', '!=', 'GATEWAY');
            });
        }

        // Apply search phrase filter
        if ($searchPhrase !== '' && $searchPhrase !== null) {
            $searchResults = $this->performOptimizedSearch($searchPhrase, $searchSelected);
            $searchDeviceIds = [];
            $searchSiteIds = [];
            
            foreach ($searchResults as $result) {
                if (!empty($result->device_id)) {
                    $searchDeviceIds[] = $result->device_id;
                }
                if (!empty($result->site_id)) {
                    $searchSiteIds[] = $result->site_id;
                }
            }
            
            $searchDeviceIds = array_unique($searchDeviceIds);
            $searchSiteIds = array_unique($searchSiteIds);
            
            if (!empty($searchDeviceIds) || !empty($searchSiteIds)) {
                $query->where(function($q) use ($searchDeviceIds, $searchSiteIds) {
                    if (!empty($searchDeviceIds)) {
                        $q->whereIn('device_id', $searchDeviceIds);
                    }
                    if (!empty($searchSiteIds)) {
                        $q->orWhereIn('device_ds_id', $searchSiteIds);
                    }
                });
            } else {
                // No search results, return empty query
                $query->whereRaw('1 = 0');
            }
        }

        // Apply sorting at query level with fallback
        $validDeviceColumns = ['device_equipment', 'device_identity', 'device_modified', 'device_created'];
        if (!in_array($sortField, $validDeviceColumns)) {
            $sortField = 'device_equipment'; // Default fallback for devices
        }
        
        // Apply sorting with system devices always first (if they match the filters)
        
//        // DONT REMOVE COMMENTED BELOW SYSTEM SORTING - THIS FUNCTIONALITY MIGHT BE REVERTED - Check if account has system devices (cached for 1 hour)
//        $accountId = session('account.id');
//        $hasSystemDevices = cache()->remember("account_{$accountId}_has_system_devices", 3600, function() {
//            return Device::system()->exists();
//        });
//
//        if ($hasSystemDevices) {
//            $systemQuery = Device::system()->select('device_id');
//            $systemSubquery = $systemQuery->toSql();
//            $systemBindings = $systemQuery->getBindings();
//
//            // Add the bindings to our main query
//            $query->orderByRaw("
//                CASE
//                    WHEN device_id IN ({$systemSubquery})
//                    THEN 0
//                    ELSE 1
//                END,
//                {$sortField} {$sortDirection},
//                device_id {$sortDirection}
//            ", $systemBindings);
//        } else {
            $query->orderBy($sortField, $sortDirection)->orderBy('device_id', $sortDirection);
//        }
        

        return $query;
    }

    public function searchDeviceSites(array $filters)
    {
        // Use the new query builder method and return collection for backward compatibility
        return $this->buildDeviceSitesQuery($filters)->get()->keyBy('ds_id');
    }

    public function searchDevices(array $filters, $getGatewayAlertDevices = false, $query = null, $excludeGateways = false)
    {
        // Use the new query builder method
        $deviceQuery = $this->buildDevicesQuery($filters, $getGatewayAlertDevices, $query, $excludeGateways);
        $devices = $deviceQuery->get()->keyBy('device_id');

        // Gateway alert expansion is now handled in buildDevicesQuery method
        // No additional processing needed here - devices from gateway alert sites are included in the main query

        return $devices;
    }

    private function performSearch($searchPhrase, $searchSelected): SearchResultCollection
    {
        // Use optimized single-query search
        $searchResults = $this->performOptimizedSearch($searchPhrase, $searchSelected);
        
        // Convert to SearchResultCollection for backward compatibility
        $collection = new SearchResultCollection();
        foreach ($searchResults as $result) {
            $collection->push($result);
        }
        
        return $collection;
    }

    private function performOptimizedSearch($searchPhrase, $searchSelected): array
    {
        $searchTerm = '%' . mb_strtolower($searchPhrase) . '%';
        $allSelected = in_array('all', $searchSelected) || empty($searchSelected);
        
        $unionQueries = [];
        $params = [];
        
        // 1. Device search - direct mapping to device_site
        if ($allSelected || array_intersect(['identity', 'equipment', 'module_number', 'pin'], $searchSelected)) {
            $deviceConditions = [];
            if ($allSelected || in_array('identity', $searchSelected)) {
                $deviceConditions[] = "LOWER(d.device_identity) LIKE ?";
                $params[] = $searchTerm;
            }
            if ($allSelected || in_array('equipment', $searchSelected)) {
                $deviceConditions[] = "LOWER(d.device_equipment) LIKE ?";
                $params[] = $searchTerm;
            }
            if ($allSelected || in_array('module_number', $searchSelected)) {
                $deviceConditions[] = "LOWER(d.device_module) LIKE ?";
                $params[] = $searchTerm;
            }
            if ($allSelected || in_array('pin', $searchSelected)) {
                $deviceConditions[] = "LOWER(d.device_pin) LIKE ?";
                $params[] = $searchTerm;
            }
            
            if (!empty($deviceConditions)) {
                $unionQueries[] = "SELECT DISTINCT d.device_ds_id as site_id, d.device_id, 'device' as source_type
                                  FROM devices d 
                                  WHERE (" . implode(' OR ', $deviceConditions) . ")
                                  AND d.device_deleted = '0000-00-00 00:00:00'";
            }
        }
        
        // 2. DeviceSite search - direct mapping
        if ($allSelected || array_intersect(['site', 'link'], $searchSelected)) {
            $siteConditions = [];
            if ($allSelected || in_array('site', $searchSelected)) {
                $siteConditions[] = "LOWER(ds.ds_name) LIKE ?";
                $params[] = $searchTerm;
            }
            if ($allSelected || in_array('link', $searchSelected)) {
                $siteConditions[] = "LOWER(ds.ds_link) LIKE ?";
                $params[] = $searchTerm;
            }
            
            if (!empty($siteConditions)) {
                $unionQueries[] = "SELECT ds.ds_id as site_id, NULL as device_id, 'site' as source_type
                                  FROM device_sites ds
                                  WHERE (" . implode(' OR ', $siteConditions) . ")
                                  AND ds.ds_deleted IS NULL";
            }
        }
        
        // 3. Module search - maps to both device_sites (via ds_protocol_id) and devices.device_site  
        if ($allSelected || in_array('module_type', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT COALESCE(ds.ds_id, d.device_ds_id) as site_id, d.device_id, 'module' as source_type
                              FROM modules m
                              LEFT JOIN device_sites ds ON m.module_id = ds.ds_protocol_id AND ds.ds_deleted IS NULL
                              LEFT JOIN devices d ON m.module_id = d.device_module_id AND d.device_deleted = '0000-00-00 00:00:00'
                              WHERE (LOWER(m.module_name) LIKE ? OR LOWER(m.module_desc) LIKE ?)
                              AND (ds.ds_id IS NOT NULL OR d.device_ds_id IS NOT NULL)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // 4. ModuleType search - maps through modules.devices.device_site and modules.device_sites
        if ($allSelected || in_array('device_type', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT COALESCE(ds.ds_id, d.device_ds_id) as site_id, d.device_id, 'module_type' as source_type
                              FROM module_types mt
                              JOIN modules m ON mt.mt_id = m.module_mt_id
                              LEFT JOIN device_sites ds ON m.module_id = ds.ds_protocol_id AND ds.ds_deleted IS NULL
                              LEFT JOIN devices d ON m.module_id = d.device_module_id AND d.device_deleted = '0000-00-00 00:00:00'
                              WHERE (LOWER(mt.mt_type) LIKE ? OR LOWER(mt.mt_desc) LIKE ?)
                              AND (ds.ds_id IS NOT NULL OR d.device_ds_id IS NOT NULL)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // 5. Number search - maps to device_site with type filtering
        if ($allSelected || array_intersect(['numbers', 'pstn', 'sim', 'sip', 'pbx'], $searchSelected)) {
            $numberTypeConditions = [];
            if (!$allSelected && !in_array('numbers', $searchSelected)) {
                // Filter by specific number types
                $numberTypes = array_intersect(['pstn', 'sim', 'sip', 'pbx'], $searchSelected);
                if (!empty($numberTypes)) {
                    $numberTypeConditions[] = "LOWER(nt.nt_type) IN ('" . implode("','", array_map('strtolower', $numberTypes)) . "')";
                }
            }
            
            $whereClause = "LOWER(n.number_value) LIKE ?";
            if (!empty($numberTypeConditions)) {
                $whereClause .= " AND (" . implode(' OR ', $numberTypeConditions) . ")";
            }
            
            $unionQueries[] = "SELECT DISTINCT n.number_ds_id as site_id, NULL as device_id, 'number' as source_type
                              FROM numbers n
                              LEFT JOIN number_types nt ON n.number_nt_id = nt.nt_id
                              WHERE {$whereClause}";
            $params[] = $searchTerm;
        }
        
        // 6. DeviceComment search - maps through device.device_site
        if ($allSelected || in_array('comments', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT d.device_ds_id as site_id, d.device_id, 'comment' as source_type
                              FROM device_comments dc
                              JOIN devices d ON dc.dc_device_id = d.device_id AND d.device_deleted = '0000-00-00 00:00:00'
                              WHERE LOWER(dc.dc_text) LIKE ?";
            $params[] = $searchTerm;
        }
        
        // 7. Address search - maps to device_sites (1:N)
        if ($allSelected || in_array('address', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT ds.ds_id as site_id, NULL as device_id, 'address' as source_type
                              FROM addresses a
                              JOIN device_sites ds ON a.address_id = ds.ds_address_id AND ds.ds_deleted IS NULL
                              WHERE LOWER(a.address_value) LIKE ?";
            $params[] = $searchTerm;
        }
        
        // 8. Location search - maps through addresses.device_sites
        if ($allSelected || in_array('address', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT ds.ds_id as site_id, NULL as device_id, 'location' as source_type
                              FROM locations l
                              JOIN addresses a ON l.location_id = a.address_location_id
                              JOIN device_sites ds ON a.address_id = ds.ds_address_id AND ds.ds_deleted IS NULL
                              WHERE (LOWER(l.location_value) LIKE ? OR LOWER(l.location_postcode) LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // 9. DeviceGateway search - maps through device.device_site
        if ($allSelected || array_intersect(['mac', 'imei'], $searchSelected)) {
            $gatewayConditions = [];
            if ($allSelected || in_array('mac', $searchSelected)) {
                $gatewayConditions[] = "LOWER(dg.dg_mac) LIKE ?";
                $params[] = $searchTerm;
            }
            if ($allSelected || in_array('imei', $searchSelected)) {
                $gatewayConditions[] = "LOWER(dg.dg_imei) LIKE ?";
                $params[] = $searchTerm;
            }
            
            if (!empty($gatewayConditions)) {
                $unionQueries[] = "SELECT DISTINCT d.device_ds_id as site_id, d.device_id, 'gateway' as source_type
                                  FROM device_gateways dg
                                  JOIN devices d ON dg.dg_device_id = d.device_id AND d.device_deleted = '0000-00-00 00:00:00'
                                  WHERE (" . implode(' OR ', $gatewayConditions) . ")
                                  AND dg.dg_deleted IS NULL";
            }
        }
        
        // 10. CustomFieldValue search - maps to deviceSite OR device.device_site
        if ($allSelected || in_array('field_value', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT COALESCE(cfv.cfv_ds_id, d.device_ds_id) as site_id, d.device_id, 'custom_field' as source_type
                              FROM custom_field_values cfv
                              LEFT JOIN devices d ON cfv.cfv_device_id = d.device_id AND d.device_deleted = '0000-00-00 00:00:00'
                              WHERE LOWER(cfv.cfv_value) LIKE ?
                              AND (cfv.cfv_ds_id IS NOT NULL OR d.device_ds_id IS NOT NULL)";
            $params[] = $searchTerm;
        }
        
        // 11. DeviceLabel search - maps to device_sites (N:N)
        if ($allSelected || in_array('label', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT dls.dld_ds_id as site_id, NULL as device_id, 'label' as source_type
                              FROM device_labels dl
                              JOIN device_labels_sites dls ON dl.dl_id = dls.dld_dl_id
                              WHERE LOWER(dl.dl_name) LIKE ?";
            $params[] = $searchTerm;
        }
        
        // 12. DeviceLabelGroup search - complex: labels that belong to group
        if ($allSelected || in_array('group', $searchSelected)) {
            $unionQueries[] = "SELECT DISTINCT dls.dld_ds_id as site_id, NULL as device_id, 'label_group' as source_type
                              FROM device_label_groups dlg
                              JOIN device_labels dl ON dlg.dlg_id = dl.dl_dlg_id
                              JOIN device_labels_sites dls ON dl.dl_id = dls.dld_dl_id
                              WHERE LOWER(dlg.dlg_name) LIKE ?";
            $params[] = $searchTerm;
        }
        
        if (empty($unionQueries)) {
            return [];
        }
        
        $fullQuery = implode(' UNION ALL ', $unionQueries);
        $results = DB::select($fullQuery, $params);
        
        // Convert to SearchResult objects for backward compatibility
        $searchResults = [];
        foreach ($results as $result) {
            // Create mock SearchResult objects
            $searchResults[] = (object) [
                'site_id' => $result->site_id,
                'device_id' => $result->device_id,
                'source_type' => $result->source_type
            ];
        }
        
        return $searchResults;
    }

    private function transformSearchResultsForSites($searchResults, $searchSelected): array
    {
        // Extract site IDs directly from optimized search results
        $siteIds = [];
        foreach ($searchResults as $result) {
            if (!empty($result->site_id)) {
                $siteIds[] = $result->site_id;
            }
        }
        
        // Remove duplicates and null values
        $siteIds = array_unique(array_filter($siteIds));
        
        if (empty($siteIds)) {
            return [];
        }
        
        // Load DeviceSite models for the found IDs
        $deviceSites = DeviceSite::whereIn('ds_id', $siteIds)->get()->keyBy('ds_id');
        
        // Return array of DeviceSite objects
        return $deviceSites->values()->all();
    }

    private function transformSearchResultsForDevices($searchResults, $states, $searchSelected, $excludeGateways = false): array
    {
        // Extract device IDs and site IDs from optimized search results
        $deviceIds = [];
        $siteIds = [];
        
        foreach ($searchResults as $result) {
            if (!empty($result->device_id)) {
                $deviceIds[] = $result->device_id;
            }
            if (!empty($result->site_id)) {
                $siteIds[] = $result->site_id;
            }
        }
        
        // Remove duplicates
        $deviceIds = array_unique(array_filter($deviceIds));
        $siteIds = array_unique(array_filter($siteIds));
        
        $resultDevices = [];
        
        // Load devices directly by ID
        if (!empty($deviceIds)) {
            $devices = Device::whereIn('device_id', $deviceIds)->get();
            foreach ($devices as $device) {
                $validDevice = $this->getDeviceByState($device, $states, $excludeGateways);
                if ($validDevice) {
                    $resultDevices[] = $validDevice;
                }
            }
        }
        
        // Load devices by site IDs (for cases where we only have site_id)
        if (!empty($siteIds)) {
            $devicesFromSites = Device::whereIn('device_ds_id', $siteIds)->get();
            foreach ($devicesFromSites as $device) {
                // Skip if we already have this device from direct device_id lookup
                $alreadyAdded = false;
                foreach ($resultDevices as $existingDevice) {
                    if ($existingDevice && $existingDevice->device_id === $device->device_id) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                
                if (!$alreadyAdded) {
                    $validDevice = $this->getDeviceByState($device, $states, $excludeGateways);
                    if ($validDevice) {
                        $resultDevices[] = $validDevice;
                    }
                }
            }
        }
        
        // Filter out null values
        return array_filter($resultDevices);
    }

    private function getDeviceByState($device, $states, $excludeGateways = false)
    {
        if ($excludeGateways && $device->module?->module_type?->mt_type === 'GATEWAY') {
            // hmmm, here to my mind comes the idea to not actually return nothing but return
            // devices that are under site that gateway belongs to, kinda similar to what is
            // happening under DeviceGateway branch in transformForDevices
            return null;
        }
        if (in_array('enabled', $states)) {
            if ($device->device_enabled == 1) {
                return $device;
            }
        } elseif (in_array('disabled', $states)) {
            if ($device->device_enabled == 0 && $device->device_deleted == '0000-00-00 00:00:00') {
                return $device;
            }
        } else {
            if ($device->device_deleted != '0000-00-00 00:00:00') {
                return $device;
            }
        }
    }
}