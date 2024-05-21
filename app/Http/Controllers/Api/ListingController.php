<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;

class ListingController extends Controller
{
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

    public function show($id)
    {
        $listing = Listing::find($id);
        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }
        return response()->json($listing);
    }

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
