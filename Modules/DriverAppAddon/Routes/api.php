<?php

use Illuminate\Http\Request;
use Modules\Cargo\Http\Controllers\Api\ShipmentController;
use Modules\Cargo\Http\Controllers\Api\MissionsController;

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

// Missions Apis Routes
Route::post('changeMissionStatus', [MissionsController::class, 'changeMissionApi']);
Route::post('remove-shipment-from-mission', [MissionsController::class, 'RemoveShipmetnFromMission']);
Route::get('missions', [MissionsController::class, 'getCaptainMissions']);
// Route::get('MissionShipments', [MissionsController::class, 'getMissionShipments']);
Route::get('MissionShipments', [ShipmentController::class, 'getMissionShipments']);

// Get Reasons Api Route
Route::get('reasons', [MissionsController::class, 'getReasons']);

// Show Register In Driver App Api Route
Route::get('show-register-in-driver-app', [ShipmentController::class, 'showRegisterInDriverApp']);