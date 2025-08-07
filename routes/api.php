<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/login',[AuthController::class,'login']);
Route::post('auth/register',[AuthController::class,'register']);
Route::get('sensor/fetchsensor',[SensorController::class,'getLatestValueSensor']);
Route::get('user/profile',[AuthController::class,'profile'])->middleware('auth:sanctum');
Route::post('user/changepassword',[AuthController::class,'changepassword'])->middleware('auth:sanctum');
Route::get('maps/location',[SensorController::class,'getAllLocation']);
Route::post('auth/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::post('post-sensor',[SensorController::class,'savedataSensor']);
Route::get('load-fuzzy',[SensorController::class,'load_fuzzy']);
Route::post('forgotpassword',[AuthController::class,'forgotpassword']);
Route::post('login_with_code',[AuthController::class,'login_with_code']);