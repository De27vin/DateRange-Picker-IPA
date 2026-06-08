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

Route::get('/login', \App\Http\Livewire\Auth\Login::class)->name('login');
Route::get('join/{token}', [\App\Http\Controllers\InviteController::class, 'join'])->name('join');
Route::post('accept', [\App\Http\Controllers\InviteController::class, 'accept'])->name('accept');

Route::middleware('auth')->group(function () {
    Route::post('/exports', [\App\Http\Controllers\ExportController::class, 'store'])
        ->name('exports.store');
    Route::get('/exports/{type}/{downloadId}/progress', [\App\Http\Controllers\ExportController::class, 'progress'])
        ->name('exports.progress');
    Route::get('/exports/{type}/{downloadId}/download', [\App\Http\Controllers\ExportController::class, 'download'])
        ->name('exports.download');
});
    
Route::get('/import-devices-progress', [DownloadProgressController::class, 'getImportDevicesProgress'])
    ->name('importDevicesProgress')
    ->middleware('auth');

Route::middleware(['auth','setTimezone'])->group(function() {

//    Route::get('/import-devices', ImportDevicesNew::class)->middleware('can:admin,site');

    // NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW NEW
    // Pages routes
    Route::get('dashboard', function () { return view('pages.dashboard-new'); })->name('dashboard');
    Route::get('equipment', function () { return view('pages.equipment-new'); })->name('equipment');
    Route::get('charts', function () { return view('pages.charts-new'); })->name('charts');

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
    Route::post('/callcenter/connect-agent', [\App\Http\Controllers\Api\AlarmAgentController::class, 'connectAgent'])->name('callcenter.connect-agent');
    Route::post('/callcenter/classify-alarm', [\App\Http\Controllers\Api\AlarmAgentController::class, 'classifyAlarm'])->name('callcenter.classify-alarm');
    Route::get('/amwin-classification/{device_equipment}', \App\Http\Livewire\Admin\AmwinClassification::class)->name('amwin-classification');

    Route::get('/devices-site-create', \App\Http\Livewire\Create\CreateSite::class)->name('devices-site-create');

    Route::get('/device-site/{device_site_id}', \App\Http\Livewire\Ucp\DeviceSiteDetails::class)->name('device-site-details');

    // Import devices routes
    Route::get('import-devices-template', [\App\Http\Controllers\ImportDevicesController::class, 'downloadTemplate'])->name('import.devices.template');
    Route::get('import-devices-instructions', [\App\Http\Controllers\ImportDevicesController::class, 'downloadInstructions'])->name('import.devices.instructions');

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
    app(\App\Services\UserContextService::class)->logoutActiveUser();
    return redirect('/login');
});


if (env('EXTERNAL_LINK_URL') && $json = json_decode(env('EXTERNAL_LINK_URL'), true)) {
    foreach ($json as $account => $link) {
        Route::get($link,\App\Http\Livewire\BasfLink\Dashboard::class)->name($account.'-external-link');
        Route::get($link.'/device/{device_id}',\App\Http\Livewire\BasfLink\DeviceDetails::class)->name($account.'-external-link-device')->middleware(['auth']);
    }
}

// Test SignalWireService
//Route::get('/test-signalwire-service', function () {
//    $signalWireService = new \App\Services\SignalWireService();
//
//    // Get a user for testing (adjust user_id as needed)
//    $user = \App\Models\User::find(1);
//
//    if (!$user) {
//        return response()->json(['error' => 'User not found']);
//    }
//
//    return response()->json([
//        'user_id' => $user->user_id,
//        'user_name' => $user->name,
//        'primary_email' => $user->getPrimaryEmail(),
//        'sip_username' => $user->getSipUsername(),
//        'has_mandown_role' => $user->hasMandownRole(),
//        'test_password_hash' => hash('sha256', 'test123'),
//    ]);
//});
