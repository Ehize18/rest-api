<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Listing;

class ReviewController extends Controller
{
/**
* @OA\Post(
     *     path="/api/listings/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Создание отзыва",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id объявления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="rate",
     *         in="query",
     *         description="Рейтинг от 1 до 5",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="text",
     *         in="query",
     *         description="Текст отзыва",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Отзыв создан"),
     *     @OA\Response(response="404", description="Объявление не найдено"),
     * )
     */
    public function store(Request $request, int $id)
    {
        $request->validate([
            'rate' => 'required|integer|min:1|max:5',
            'text' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        $review = Review::create([
            'rate' => $request->rate,
            'text' => $request->text,
            'author_id' => $userId,
            'listing_id' => $id
        ]);

        return response()->json($review);
    }

/**
* @OA\Get(
     *     path="/api/listings/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Получение отзывов на объявлении",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id объявления",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Отзывы создан"),
     *     @OA\Response(response="404", description="Объявление не найдено"),
     * )
     */
    public function index($listingId)
    {
        $listing = Listing::find($listingId);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        $reviews = Review::where('listing_id', $listingId)->get();

        return response()->json($reviews);
    }
}
