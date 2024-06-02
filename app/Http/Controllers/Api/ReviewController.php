<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Listing;

class ReviewController extends Controller
{
    public function store(Request $request, $listingId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $listing = Listing::find($listingId);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        $review = Review::create([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'user_id' => $userId,
            'listing_id' => $listingId,
        ]);

        return response()->json($review);
    }

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
