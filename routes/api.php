<?php

use Illuminate\Http\Request;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\SetupAccountDataForSession;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartsSettingsController;
use App\Http\Controllers\DashboardWidgetsController;
use App\Http\Controllers\TimeSeriesController;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\BasfController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Alarm notification routes
Route::get('/alarm-notifications/current', [\App\Http\Controllers\Api\AlarmBroadcastController::class, 'getCurrentAlarms'])
    ->name('api.alarm-notifications.current')
    ->middleware('web');

Route::post('/realtime/broadcast', [\App\Http\Controllers\Api\RealtimeBroadcastController::class, 'broadcast'])
    ->name('api.realtime.broadcast')
    ->middleware('internalApiToken');

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    SetupAccountDataForSession::class,
])->group(function (): void {
    Route::get('/timeseries', [TimeSeriesController::class, 'fetch'])->name('api.timeseries');
    Route::get('/dashboard/widgets/summary', [DashboardWidgetsController::class, 'summary'])->name('api.dashboard.widgets.summary');
    Route::get('/dashboard/widgets/settings', [DashboardWidgetsController::class, 'settings'])->name('api.dashboard.widgets.settings');
    Route::get('/dashboard/widgets/series', [DashboardWidgetsController::class, 'series'])->name('api.dashboard.widgets.series');
    Route::get('/charts/settings', [ChartsSettingsController::class, 'settings'])->name('api.charts.settings');
});
