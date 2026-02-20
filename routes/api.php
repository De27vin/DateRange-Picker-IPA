<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

// Route::middleware('auth:basf')->get('/basf', \App\Http\Livewire\Ucp\ActiveAlarmDevices::class)->name('basf');
// Route::middleware('auth:api')->post('/basf', 'App\Http\Controllers\BasfController@index');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


