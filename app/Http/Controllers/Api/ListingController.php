<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    // Создание нового объявления
    public function store(Request $request)
    {
        // Валидация входных данных
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ]);

        // Добавление ID автора
        $data['owner_id'] = Auth::id();

        // Создание нового объявления
        $listing = Listing::create($data);

        return response()->json($listing, 201);
    }

    // Обновление существующего объявления
    public function update(Request $request, $id)
    {
        // Найти объявление по ID
        $listing = Listing::findOrFail($id);

        // Проверка авторства
        if ($listing->owner_id != Auth::id()) {
            return response()->json(['error' => 'You are not authorized to update this listing'], 403);
        }

        // Валидация входных данных
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
        ]);

        // Обновление объявления
        $listing->update($data);

        return response()->json($listing);
    }
}

