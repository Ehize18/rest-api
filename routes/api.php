<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

// Маршрут для создания города
Route::post('/cities', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string',
    ]);

    $city = City::create($data);

    return response()->json($city, 201);
});

// Маршрут для получения всех городов
Route::get('/cities', function () {
    $cities = City::all();

    return response()->json($cities);
});

// Маршрут для создания объявления
Route::post('/listings', function (Request $request) {
    $data = $request->validate([
        'title' => 'required|string',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'city_id' => 'required|exists:cities,id',
    ]);

    $listing = Listing::create($data);

    return response()->json($listing, 201);
});

// Маршрут для получения всех объявлений с пагинацией
Route::get('/listings', function () {
    $listings = Listing::paginate(10);

    return response()->json($listings);
});

// Маршрут для редактирования объявления
Route::put('/listings/{id}', function (Request $request, $id) {
    $listing = Listing::findOrFail($id);

    $data = $request->validate([
        'title' => 'string',
        'description' => 'string',
        'price' => 'numeric',
        'city_id' => 'exists:cities,id',
    ]);

    $listing->update($data);

    return response()->json($listing);
});
