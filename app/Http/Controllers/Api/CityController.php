<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    // Создание нового города
    public function createCity(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $city = City::create($data);

        return response()->json($city, 201);
    }

    // Получение всех городов
    public function getCities()
    {
        $cities = City::all();

        return response()->json($cities);
    }

    // Получение конкретного города по ID
    public function getCity($id)
    {
        $city = City::findOrFail($id);

        return response()->json($city);
    }

    // Обновление города
    public function updateCity(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $city = City::findOrFail($id);
        $city->update($data);

        return response()->json($city);
    }

    // Удаление города
    public function deleteCity($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return response()->json(null, 204);
    }
}
