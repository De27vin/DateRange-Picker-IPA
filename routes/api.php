<?php

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\SetupAccountDataForSession;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BasfController;
use App\Http\Controllers\DashboardWidgetsController;
use App\Http\Controllers\TimeSeriesController;

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

// Route::middleware('auth:basf')->get('/basf', \App\Http\Livewire\Ucp\ActiveAlarmDevices::class)->name('basf');
// Route::middleware('auth:api')->post('/basf', 'App\Http\Controllers\BasfController@index');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    SetupAccountDataForSession::class,
])->group(function (): void {
    Route::get('/timeseries', [TimeSeriesController::class, 'fetch'])->name('api.timeseries');
    Route::get('/dashboard/widgets/summary', [DashboardWidgetsController::class, 'summary'])->name('api.dashboard.widgets.summary');
    Route::get('/dashboard/widgets/series', [DashboardWidgetsController::class, 'series'])->name('api.dashboard.widgets.series');
});
