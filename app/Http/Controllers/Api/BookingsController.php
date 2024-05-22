<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use Carbon\Carbon;
class BookingsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($renter_id)
    {

        $renter = User::find($renter_id);
        if(!$renter){
            return response()->json(["error"=>"renter not found"],404);
        }
        $bookings = Booking::where('renter_id',$renter_id)->orderBy("created_at")->get();
        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'listing_id'=>'required|integer',
            'renter_id'=>'required|ingeter',
            'check_in'=>'required|date_format:Y-m-d H:i:s',
            'check_out'=>'required|date_format:Y-m-d H:i:s',
            'total_price'=>'required|integer',
            'status'=>'required|string'
        ]);
        $listing = Listing::find($request->listing_id);
        if(!$listing){
            return response()->json(['error' => 'listing to book not found'],404);
        }
        $renter = $request->user()->id;
        if(!User::find($renter)){
            return response()->json(['error' => 'renter not found'],404);
        }
        if($request->renter_id!==$renter){
            return response()->json(['error' => 'Only owner of the account can book a listing'],400);
        }

        $bookings = Booking::where('listing_id', $request->listing_id)->get();

        foreach ($bookings as $booking) {
            $booking_check_in = Carbon::createFromTimestamp($booking->check_in);
            $booking_check_out = Carbon::createFromTimestamp($booking->check_out);

            if ($request->check_in->between($booking_check_in, $booking_check_out) ||
                $request->check_out->between($booking_check_in, $booking_check_out) ||
                ($request->check_in->lte($booking_check_in) && $request->check_out->gte($booking_check_out))) {
                return response()->json(['error'=> 'dates for booking is not available'],400); // Объект недоступен для бронирования
            }
        }
        $booking = Booking::create([
            'listing_id'=>$request->listing_id,
            'renter_id'=>$request->renter_id,
            'check_in'=>$request->check_in,
            'check_out'=>$request->check_out,
            'total_price'=>$request->total_price,
            'status'=>$request->status
        ]);
        return response()->json($booking);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */



    public function show($id){
    $booking = Booking::find($id);
    if(!$booking){
            return response()->json(['error' => 'Booking not found'],404);
        }
        return response()->json($booking);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'listing_id'=>'required|integer',
            'renter_id'=>'required|ingeter',
            'check_in'=>'required|date_format:Y-m-d H:i:s',
            'check_out'=>'required|date_format:Y-m-d H:i:s',
            'total_price'=>'required|integer',
            'status'=>'required|string'
        ]);
        $booking = Booking::find($id);
        if(!$booking){
            return response()->json(['error' => 'Booking to update not found'],404);
        }
        $user_id = $request->user()->id;
        if ($user_id !== $request->renter_id){
            return response()->json([
                'error'=>'You are not the creator of the booking'
            ], 400);
        }
        $l_id = $request->listing_id;
        if ($l_id !== $booking->listing_id){
            return response()->json([
                'error'=>'Incorrect listing of the booking'
            ], 400);
        }
        $other_bookings = Booking::where('listing_id', $booking->listing_id)->where('id', '!=', $id)->get();
        foreach ($other_bookings as $other_booking) {
            $other_check_in = Carbon::createFromTimestamp($other_booking->check_in);
            $other_check_out = Carbon::createFromTimestamp($other_booking->check_out);
            if ($request->check_in->between($other_check_in, $other_check_out) ||
            $request->check_out->between($other_check_in, $other_check_out) ||
                ($request->check_in->lte($other_check_in) && $$request->check_out->gte($other_check_out))) {
                return response()->json(['error' => 'The new dates conflict with an existing bookings.'], 400);
            }
        }
        $booking->check_in = $request->check_in;
        $booking->check_out = $request->check_out;
        $booking->save();
        return response()->json([
            'message'=>'The booking has been changed'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $booking = Booking::find($id);
        if(!$booking){
            return response()->json(['error'=> 'Booking not found'],404);
        }
        $booking->delete();
        return response()->json(['message' => 'Booking deleted']);
    }
}
