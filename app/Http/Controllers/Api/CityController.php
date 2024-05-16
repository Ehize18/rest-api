<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Controllers\Controller;

class CityController extends Controller
{
    public function index()
    {
        // Получаем все города
        $cities = City::all();

        // Возвращаем JSON с массивом городов
        return response()->json($cities);
    }

    public function store(Request $request)
    {
        // Создаем новый город на основе данных из запроса
        $city = new City();
        $city->name = $request->input('name');
        $city->save();

        // Возвращаем JSON с созданным городом
        return response()->json($city);
    }

    public function show($id)
    {
        // Находим город по его ID
        $city = City::find($id);

        // Если город не найден, возвращаем ошибку 404
        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        // Возвращаем JSON с найденным городом
        return response()->json($city);
    }

    public function update(Request $request, $id)
    {
        // Находим город по его ID
        $city = City::find($id);

        // Если город не найден, возвращаем ошибку 404
        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        // Обновляем данные города на основе данных из запроса
        $city->name = $request->input('name');
        $city->save();

        // Возвращаем JSON с обновленным городом
        return response()->json($city);
    }

    public function destroy($id)
    {
        // Находим город по его ID
        $city = City::find($id);

        // Если город не найден, возвращаем ошибку 404
        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        // Удаляем найденный город
        $city->delete();

        // Возвращаем сообщение об успешном удалении
        return response()->json(['message' => 'City deleted']);
    }
}
