<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;

class ListingController extends Controller
{
/**
* @OA\Get(
     *     path="/api/listings",
     *     tags={"Listings"},
     *     summary="Получение всех объявлений",
     *     @OA\Parameter(
     *         name="paginate",
     *         in="query",
     *         description="Включить пагинацию",
     *         required=false,
     *         @OA\Schema(type="bool")
     *     ),
     *     @OA\Response(response="200", description="Список объявлений"),
     * )
     */
    public function index(Request $request)
    {
        if ($request->has('paginate') && $request->input('paginate') === 'false') {
            $listings = Listing::all();
        } else {
            $perPage = 10;
            $listings = Listing::paginate($perPage);
        }

        return response()->json($listings);

    }

/**
* @OA\Post(
     *     path="/api/listings",
     *     tags={"Listings"},
     *     summary="Добавление объявления",
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Название объявления",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Описание объявления",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="city_id",
     *         in="query",
     *         description="Id города",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="Адрес недвижимости",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price_per_day",
     *         in="query",
     *         description="Цена за день аренды",
     *         required=true,
     *         @OA\Schema(type="numeric")
     *     ),
     *     @OA\Response(response="200", description="Объявление создано"),
     *     @OA\Response(response="422", description="Ощибка валидации")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'city_id' => 'required|integer',
            'address' => 'required|string',
            'price_per_day' => 'required|numeric'
        ]);

        $owner_id = $request->user()->id;

        $listing = Listing::create([
            'title' => $request->title,
            'description' => $request->description,
            'owner_id' => $owner_id,
            'city_id' => $request->city_id,
            'address' => $request->address,
            'price_per_day' => $request->price_per_day,
        ]);

        return response()->json($listing);
    }

/**
* @OA\Get(
     *     path="/api/listings/{id}",
     *     tags={"Listings"},
     *     summary="Получение объявления по id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id объявления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Объявление")
     * )
     */
    public function show($id)
    {
        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }
        return response()->json($listing);
    }

/**
* @OA\Put(
     *     path="/api/listings/{id}",
     *     tags={"Listings"},
     *     summary="Изменение названия и описания объявления",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id объявления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Новое название",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="Новое описание",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Объявление изменено"),
     *     @OA\Response(response="404", description="Объявление не найдено"),
     *     @OA\Response(response="403", description="Вы не владелец объявления")
     * )
     */
    public function update(Request $request, $id)
    {
        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        if ($listing->owner_id !== $request->user()->id) {
            return response()->json(['error' => 'You are not authorized to update this listing'], 403);
        }

        $listing->title = $request->input('title');
        $listing->description = $request->input('description');
        $listing->save();

        return response()->json($listing);
    }

/**
* @OA\Delete(
     *     path="/api/listings/{id}",
     *     tags={"Listings"},
     *     summary="Изменение названия и описания объявления",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id объявления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Объявление изменено"),
     *     @OA\Response(response="404", description="Объявление не найдено"),
     * )
     */
    public function destroy($id)
    {
        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        $listing->delete();

        return response()->json(['message' => 'Listing deleted']);
    }
}
