
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Review;

class ReviewController extends Controller
{
    // Метод для получения всех отзывов по конкретному объявлению
    public function index(Listing $listing)
    {
        $reviews = $listing->reviews()->get();
        return response()->json($reviews);
    }

    // Метод для создания нового отзыва
    public function store(Request $request, Listing $listing)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
        ]);

        $review = new Review([
            'user_id' => $request->input('user_id'),
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        $listing->reviews()->save($review);

        return response()->json($review, 201);
    }
}
