<?php

use App\Http\Controllers\DownloadProgressController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Livewire\Ucp\ImportDevicesNew;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
//use App\Http\Controllers\DocsController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\TimeSeriesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__.'/auth.php';
//require __DIR__.'/tests.php';

Route::get('/', function () {
    if(Auth::user()){
        return Redirect::to('/dashboard');
    }
    return Redirect::to('/login');
});

Route::get('charts', function () {
    return view('pages.charts-new');
})->name('charts');


Route::get('/login', \App\Http\Livewire\Auth\Login::class)->name('login');
Route::get('join/{token}', [\App\Http\Controllers\InviteController::class, 'join'])->name('join');
Route::post('accept', [\App\Http\Controllers\InviteController::class, 'accept'])->name('accept');

Route::get('/export-history-progress', [DownloadProgressController::class, 'getExportHistoryProgress'])
    ->name('exportHistoryProgress')
    ->middleware('auth');

Route::get('/export-devices-progress', [DownloadProgressController::class, 'getExportDevicesProgress'])
    ->name('exportDevicesProgress')
    ->middleware('auth');
    
Route::get('/import-devices-progress', [DownloadProgressController::class, 'getImportDevicesProgress'])
    ->name('importDevicesProgress')
    ->middleware('auth');

Route::middleware(['auth','setTimezone'])->group(function() {

//    Route::get('/import-devices', ImportDevicesNew::class)->middleware('can:admin,site');

    // NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW
    // Pages routes
    Route::get('dashboard', function () { return view('pages.dashboard-new'); })->name('dashboard');
    Route::get('equipment', function () { return view('pages.equipment-new'); })->name('equipment');

    // Api routes
    Route::middleware(['apiRequest'])->group(function() {
        Route::match(['get', 'post'], 'dashboard/devices', [\App\Http\Controllers\Api\DashboardController::class, 'getDevices'])->name('api-dashboard-devices');
        Route::match(['get', 'post'], 'dashboard/basf-devices', [\App\Http\Controllers\Api\DashboardController::class, 'getBasfDevices'])->name('api-dashboard-basf-devices');
        Route::match(['get', 'post'], 'equipment/sites', [\App\Http\Controllers\Api\EquipmentController::class, 'getSites']);
        Route::post('equipment/site', [\App\Http\Controllers\Api\EquipmentController::class, 'getSite'])->name('api-equipment-site');
//        Route::post('equipment/saveSite', [\App\Http\Controllers\Api\EquipmentController::class, 'saveSite']);
        Route::post('equipment/saveSite', [\App\Http\Controllers\Api\EquipmentController::class, 'saveSite']);
        Route::post('equipment/confirmSet', [\App\Http\Controllers\Api\EquipmentController::class, 'confirmSetField']);
        Route::post('equipment/rejectSet', [\App\Http\Controllers\Api\EquipmentController::class, 'rejectSetField']);
        Route::post('equipment/deleteDevice', [\App\Http\Controllers\Api\EquipmentController::class, 'deleteDevice']);
        Route::post('equipment/deleteSite', [\App\Http\Controllers\Api\EquipmentController::class, 'deleteSite']);
        Route::post('equipment/toggleDeviceState', [\App\Http\Controllers\Api\EquipmentController::class, 'toggleDeviceState']);
        Route::post('equipment/addComment', [\App\Http\Controllers\Api\EquipmentController::class, 'addComment']);

        // todo: methods below move to separate controller
        Route::post('equipment/siteHistory', [\App\Http\Controllers\Api\EquipmentController::class, 'getSiteHistory']);
        Route::post('equipment/fsCall', [\App\Http\Controllers\Api\EquipmentController::class, 'makeFsCall'])->name('api-equipment-fs-call');


        Route::get('data/cfg', [\App\Http\Controllers\Api\DataController::class, 'getCustomFieldsConfig'])->name('api-data-cfg');
        Route::get('data/labels', [\App\Http\Controllers\Api\DataController::class, 'getLabels'])->name('api-data-labels');
        Route::get('data/settings', [\App\Http\Controllers\Api\DataController::class, 'getSettings'])->name('api-data-settings');
        Route::get('data/countries', [\App\Http\Controllers\Api\DataController::class, 'getCountries'])->name('api-data-countries');
        Route::get('data/required', [\App\Http\Controllers\Api\DataController::class, 'getRequiredFields'])->name('api-data-required');
        Route::get('data/translations', [\App\Http\Controllers\Api\DataController::class, 'getTranslations'])->name('api-data-translations');
        Route::get('data/assignableGateways', [\App\Http\Controllers\Api\DataController::class, 'getAssignableGateways'])->name('api-assignable-gateways');
        Route::get('data/assignableSipNumbers', 'App\Http\Controllers\Api\DataController@getAssignableSipNumbers')->name('api-assignable-sip-numbers');

        Route::get('filters/filters', [\App\Http\Controllers\Api\FiltersController::class, 'getFilters'])->name('filters-filters');
        Route::get('filters/dashboard-default', [\App\Http\Controllers\Api\FiltersController::class, 'getDashboardSearchTabs'])->name('filters-dashboard-default');
        Route::get('filters/alerts-translations', [\App\Http\Controllers\Api\FiltersController::class, 'getAlertsTranslations'])->name('filters-alerts-translations');
        Route::get('filters/equipment-default', [\App\Http\Controllers\Api\FiltersController::class, 'getEquipmentSearchTabs'])->name('filters-equipment-default');
        Route::get('filters/grouped-alerts-counts', [\App\Http\Controllers\Api\FiltersController::class, 'getGroupedAlertsCounts'])->name('grouped-alerts-counts');
        Route::get('filters/search-options', [\App\Http\Controllers\Api\FiltersController::class, 'getSearchOptions'])->name('filters-search-options');
    });
    
    Route::get('data/qr-code', [\App\Http\Controllers\Api\DataController::class, 'generateQrCode'])->name('api-data-qr-code');

    Route::prefix('api/sessions')->group(function () {
        Route::get('{sessionId}/details', [SessionController::class, 'getSessionDetails']);
        Route::post('related-events', [SessionController::class, 'getRelatedEvents']);
    });

    Route::get('/accounts', \App\Http\Livewire\Admin\Accounts::class)->name('accounts');
    Route::get('/callcenter/{device_id}', \App\Http\Livewire\Admin\Callcenter::class)->name('callcenter');
    Route::get('/amwin-classification/{device_equipment}', \App\Http\Livewire\Admin\AmwinClassification::class)->name('amwin-classification');

    Route::get('/devices-site-create', \App\Http\Livewire\Create\CreateSite::class)->name('devices-site-create');

    Route::get('/device-site/{device_site_id}', \App\Http\Livewire\Ucp\DeviceSiteDetails::class)->name('device-site-details');
    Route::post('export-devices', [\App\Http\Controllers\ExportDevicesController::class, 'export'])->name('export.devices');
    Route::get('export-gateways/{tab}', [\App\Http\Controllers\ExportGatewaysController::class, 'download'])->name('export-gateways');

    // Download generated devices export by id
    Route::get('download/devices/{id}', [\App\Http\Controllers\ExportDevicesController::class, 'downloadGenerated'])
        ->name('download.devices');
    // Progress tracking routes handled by DownloadProgressController

    Route::get('/user-profile', \App\Http\Livewire\User\UserProfile::class)->name('user-profile');

    Route::group(['prefix' => 'settings'], function() {
        Route::get('/account', \App\Http\Livewire\Settings\Account::class)->name('settings.account');
        Route::get('/modules', \App\Http\Livewire\Settings\Modules::class)->name('settings.modules');
        Route::get('/labels', \App\Http\Livewire\Settings\Labels::class)->name('settings.labels');
        Route::get('/users', \App\Http\Livewire\Settings\Users::class)->name('settings.users');
        Route::get('/translations', \App\Http\Livewire\Settings\FieldsTranslations::class)->name('settings.translations'); // Direct route for testing translation export
//        Route::get('/gateways', \App\Http\Livewire\Settings\Gateways::class)->name('settings.gateways');
        Route::get('/gateways', \App\Http\Livewire\Settings\GatewaysList::class)->name('settings.gateways');
    });

    Route::get('lang/{lang}', ['as' => 'lang.switch', 'uses' => 'App\Http\Controllers\LanguageController@switchLang']);
    
    // Translation export download route
    Route::get('/download/translations/{filename}', function($filename) {
        // Security check - only allow site role users
        if (!auth()->user() || !auth()->user()->roles()->where('role_type', 'site')->exists()) {
            abort(403, 'Unauthorized');
        }
        
        $path = storage_path("app/{$filename}");
        
        if (!file_exists($path)) {
            abort(404, 'File not found');
        }
        
        return response()->download($path)->deleteFileAfterSend(true);
    })->name('download.translations');
});

Route::get('/logout', function(Request $request) {
    Cookie::queue(Cookie::forget('ucp_account'));
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
});


if (env('EXTERNAL_LINK_URL') && $json = json_decode(env('EXTERNAL_LINK_URL'), true)) {
    foreach ($json as $account => $link) {
        Route::get($link,\App\Http\Livewire\BasfLink\Dashboard::class)->name($account.'-external-link');
        Route::get($link.'/device/{device_id}',\App\Http\Livewire\BasfLink\DeviceDetails::class)->name($account.'-external-link-device')->middleware(['auth']);
    }
}


// todo: before generating ask Ben about the correct modules mapping and account id
//Route::get('import_basf_devices', function () {
//    try {
//        $data = Excel::toArray(new ExcelToArrayImport, '/home/jacek/workspace/serv24/devices4.xlsx');
//        $devices = $data[0];
//        $sites = $data[1];
//        $accountId = 2;
//
//        $deviceColumnMap = [
//            'Equipment-ID' => 'device_equipment',
//            'Notrufgerät' => 'custom1', // 61
//            'Bau' => 'custom2', // ignore
//            'Identity' => 'device_identity',
//            'Device Module' => 'device_module',
//            'Gerätetyp' => 'protocol_type',
//            'Protocol Module' => 'protocol_module',
//            'Sprechstellentyp' => 'custom3', // 2
//            'CALL_ALARM_ROUTE1_CLI_NUMBER' => 'alarm_number_1',
//            'SAP-TP' => 'custom4' // 71
//        ];
//
//        $siteColumnMap = [
//            'Equipment-ID' => 'site_equipment',
//            'SiteName' => 'ds_name',
//            'PBX' => 'pbx',
//            'Protocol' => 'site_protocol',
//            'Installationsort' => 'custom5', // 11
//        ];
//
//        $customFieldsIdsMap = [
//            'custom1' => 61,
////            'custom2' => 4,
//            'custom3' => 2,
//            'custom4' => 71,
//            'custom5' => 11, // site
//        ];
//
////        $protocolModuleMap = [
////            '2901AN' => 'TA-TELENOT-COM2901',
////            'T7008D-AN' => 'TA-TELENOT-7008',
////        ];
//
//        // devices
//        $columns = array_shift($devices);
//        foreach ($columns as &$column) {
//            $column = $deviceColumnMap[$column];
//        }
//        unset($column);
//        $eqs = [];
//        $eqsMoreThan1 = [];
//        foreach ($devices as $key => $device) {
//            $device = array_map('trim', $device);
//            $deviceEq = $device[0];
//
//            if (in_array($deviceEq, $eqs)) {
//                $eqsMoreThan1[] = $deviceEq;
//            }
//            $eqs[] = $deviceEq;
//
//            $device = array_combine($columns, $device);
//            $devices[$deviceEq] = $device;
//            unset($devices[$key]);
//        }
//
//        // sites
//        $columns = array_shift($sites);
//        foreach ($columns as &$column) {
//            $column = $siteColumnMap[$column];
//        }
//        unset($column);
//        foreach ($sites as $key => $site) {
//            $site = array_map('trim', $site);
//            $siteEq = $site[0];
//            $site = array_combine($columns, $site);
//            $sites[$siteEq] = $site;
//            unset($sites[$key]);
//        }
//
//        $sqls = [];
//        $equipmentsWoProtocolModule = [];
//        $equipmentsWUrecognizedModule = [];
//        foreach ($sites as $site) {
//
//            $devicesSite = [];
//            $devicesKeys = array_keys($devices);
//            foreach ($devicesKeys as $key) {
//                $keys2 = explode('-', $key);
//                $siteEq = $keys2[0];
//                $deviceEqPart = $keys2[1];
//
//                if ($siteEq === $site['site_equipment']) {
//                    $devicesSite[] = $devices[$key];
////                    unset($devices[$key]);
//                }
//            }
//
//            $dsName = $site['ds_name'];
//            $selectProtocolId = "(SELECT module_id FROM modules WHERE module_name = 'PROT-TELENOT' LIMIT 1)";
//
//            $sqlSite = "INSERT INTO ucp21.device_sites (ds_account_id, ds_protocol_id, ds_name, ds_created) VALUES ($accountId, $selectProtocolId, '$dsName', NOW());";
//            $sqlSiteId = "SET @site_id = LAST_INSERT_ID();";
//
//            // site pbx
//            $sqlSitePbx = "INSERT INTO ucp21.numbers (number_nt_id, number_account_id, number_ds_id, number_value, number_created) VALUES ((SELECT nt_id FROM ucp21.number_types WHERE nt_type = 'PBX'), $accountId, @site_id, {$site['pbx']}, NOW());";
//
//            // site custom fields
//            $custom5 = $site['custom5'];
//            if (!empty($custom5)) {
//                $sqlSiteCustom5 = "INSERT INTO ucp21.custom_field_values (cfv_cfc_id, cfv_ds_id, cfv_value) VALUES ({$customFieldsIdsMap['custom5']}, @site_id, '$custom5');";
//            } else {
//                $sqlSiteCustom5 = null;
//            }
//
//
//            $sqlsDevices = [];
//            foreach ($devicesSite as $device) {
//
//                $deviceEq = $device['device_equipment'];
//                if (explode('-', $deviceEq)[0] !== $device['device_identity']) {
//                    die('site equipment is not equal to identity at eq: '.$deviceEq);
//                }
//
////                $device = $devices[$deviceEq];
//                $moduleName = null;
//                if ($device['protocol_type'] === 'INTERCOM') {
//                    $moduleName = 'ICOM-TELENOT';
//                } elseif ($device['protocol_type'] === 'TELEALARM') {
//                    if (!$device['protocol_module']) {
//                        $equipmentsWoProtocolModule[] = $deviceEq;
//                    } elseif ($device['protocol_module'] === '2901AN') {
//                        $moduleName = 'TA-TELENOT-COM2901';
//                    } elseif ($device['protocol_module'] === 'T7008D-AN' || $device['protocol_module'] === 'T7008D-AN/1') {
//                        $moduleName = 'TA-TELENOT-7008';
//                    } else {
//                        $equipmentsWUrecognizedModule[] = $deviceEq;
//                    }
//                    if (empty($moduleName)) {
//                        continue;
//                    }
//                } else {
//                    die('Unrecognized protocol type for Eq: '. $deviceEq);
//                }
//                $selectModuleSql = "(SELECT module_id FROM modules WHERE module_name = '$moduleName' LIMIT 1)";
//                $identity = $device['device_identity'];
//                if (empty($identity)) {
//                    die('Empty identity for Eq: '. $deviceEq);
//                }
//                $module = $device['device_module'];
//                if (!is_numeric($module)) {
//                    die('Not numeric device module for Eq: '. $deviceEq);
//                }
//
//                $sqlsDevices[] = "INSERT INTO ucp21.devices (device_ds_id, device_module_id, device_account_id, device_equipment,device_identity, device_module, device_created, device_enabled) VALUES (@site_id, $selectModuleSql, 2, '$deviceEq', '$identity', $module, NOW(), 1);";
//                $sqlsDevices[] = "SET @device_id = LAST_INSERT_ID();";
//
//                // site custom fields
//
//
//                if (!empty($device['custom1'])) {
//                    $sqlDeviceCustom1 = "INSERT INTO ucp21.custom_field_values (cfv_cfc_id, cfv_device_id, cfv_value) VALUES ({$customFieldsIdsMap['custom1']}, @device_id, '{$device['custom1']}');";
//                    $sqlsDevices[] = $sqlDeviceCustom1;
//                }
//
//                // ignore Bau
////                if (!empty($device['custom2'])) {
////                    $sqlDeviceCustom2 = "INSERT INTO ucp21.custom_field_values (cfv_cfc_id, cfv_device_id, cfv_value) VALUES ({$customFieldsIdsMap['custom2']}, @device_id, '{$device['custom2']}');";
////                    $sqlsDevices[] = $sqlDeviceCustom2;
////                }
//
//                if (!empty($device['custom3'])) {
//                    $sqlDeviceCustom3 = "INSERT INTO ucp21.custom_field_values (cfv_cfc_id, cfv_device_id, cfv_value) VALUES ({$customFieldsIdsMap['custom3']}, @device_id, '{$device['custom3']}');";
//                    $sqlsDevices[] = $sqlDeviceCustom3;
//                }
//
//                if (!empty($device['custom4'])) {
//                    $sqlDeviceCustom4 = "INSERT INTO ucp21.custom_field_values (cfv_cfc_id, cfv_device_id, cfv_value) VALUES ({$customFieldsIdsMap['custom4']}, @device_id, '{$device['custom4']}');";
//                    $sqlsDevices[] = $sqlDeviceCustom4;
//                }
//
//                if (!empty($device['alarm_number_1'])) {
//                    $sqlDeviceAlarm = "INSERT INTO ucp21.device_settings (ds_device_id, ds_setting_id, ds_value) VALUES (@device_id, (SELECT setting_id FROM ucp21.settings WHERE setting_key = 'call.alarm.route1.cli.number'), {$device['alarm_number_1']});";
//                    $sqlsDevices[] = $sqlDeviceAlarm;
//                }
//
//            }
//
//            $sqls[] = $sqlSite;
//            $sqls[] = $sqlSiteId;
//            $sqls[] = $sqlSitePbx;
//            if (!empty($sqlSiteCustom5)) {
//                $sqls[] = $sqlSiteCustom5;
//            }
//            $sqls = array_merge($sqls, $sqlsDevices);
//
//        }
//
//
//
//        $content = implode(PHP_EOL, ['START TRANSACTION;', ...$sqls, 'COMMIT;']) .PHP_EOL;
//
//        // Write to file (creates the file if it does not exist)
//        file_put_contents('/home/jacek/workspace/serv24/devices4.sql', $content);
//
//        dd($eqsMoreThan1, $equipmentsWoProtocolModule, $equipmentsWUrecognizedModule, $sqls);
//
//    } catch (\Throwable $e) {
//        die ($e->getMessage());
//    }
//
//});
