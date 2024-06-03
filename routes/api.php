<?php

use App\Http\Controllers\Api\MessagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\BookingsController;
use App\Http\Controllers\Api\ReviewController;




// Маршруты для аутентификации
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Маршрут для получения текущего аутентифицированного пользователя
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Получение объявлений не должно быть проверено на аутентификацию
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{id}', [ListingController::class, 'show']);

Route::get('/listings/{id}/reviews', [ReviewController::class, 'index']);

//Здесь все пути, доступные ТОЛЬКО после аутентификации
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/user/messages/{receiver_id}', [MessagesController::class, 'store']);
    Route::get('/user/messages', [MessagesController::class, 'getConversationsID']);
    Route::get('/user/messages/{id}', [MessagesController::class, 'getConversation']);
    Route::put('/user/messages/{id}', [MessagesController::class, 'update']);
    Route::delete('/user/messages/{receiver_id}', [MessagesController::class, 'destroy']);

    Route::get('/listings', [ListingController::class, 'index']);
    Route::post('/listings', [ListingController::class, 'store']);
    Route::get('/listings/{id}', [ListingController::class, 'show']);
    Route::put('/listings/{id}', [ListingController::class, 'update']);
    Route::delete('/listings/{id}', [ListingController::class, 'destroy']);

    Route::post('/cities', [CityController::class, 'store']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/cities/{id}', [CityController::class, 'show']);
    Route::put('/cities/{id}', [CityController::class, 'update']);
    Route::delete('/cities/{id}', [CityController::class, 'destroy']);

    Route::get('/bookings', [BookingsController::class, 'index']);
    Route::post('/bookings', [BookingsController::class, 'store']);
    Route::put('/bookings/{id}', [BookingsController::class, 'update']);
    Route::get('/bookings/{id}', [BookingsController::class, 'show']);
    Route::delete('/bookings/{id}', [BookingsController::class, 'destroy']);

    Route::post('/listings/{id}/reviews', [ReviewController::class, 'store']);
});
