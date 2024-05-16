<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\UserController;
use App\Models\City;
use App\Models\Listing;

// Маршруты для аутентификации
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Маршрут для получения текущего аутентифицированного пользователя
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/cities', [CityController::class, 'createCity']);
Route::get('/cities', [CityController::class, 'getCities']);
Route::get('/cities/{id}', [CityController::class, 'getCity']);
Route::put('/cities/{id}', [CityController::class, 'updateCity']);
Route::delete('/cities/{id}', [CityController::class, 'deleteCity']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cities', [CityController::class, 'createCity']);
    Route::get('/cities', [CityController::class, 'getCities']);
    Route::get('/cities/{id}', [CityController::class, 'getCity']);
    Route::put('/cities/{id}', [CityController::class, 'updateCity']);
    Route::delete('/cities/{id}', [CityController::class, 'deleteCity']);
});

Route::post('/listings', [ListingController::class, 'store']);
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show']);
Route::put('/listings/{id}', [ListingController::class, 'update']);
Route::delete('/listings/{id}', [ListingController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/listings', [ListingController::class, 'store']);
    Route::get('/listings', [ListingController::class, 'index']);
    Route::get('/listings/{id}', [ListingController::class, 'show']);
    Route::put('/listings/{id}', [ListingController::class, 'update']);
    Route::delete('/listings/{id}', [ListingController::class, 'destroy']);
});
