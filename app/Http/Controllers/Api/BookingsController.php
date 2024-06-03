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
     *
     *
     *
     */
    public function index(Request $request)// вывод всех бронирований пользователя
    {
        $renter_id = $request->user()->id;
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
    public function store(Request $request)//создание бронирования
    {
        $request->validate([
            'listing_id'=>'required|numeric',
            'check_in'=>'required|date_format:Y-m-d H:i:s',
            'check_out'=>'required|date_format:Y-m-d H:i:s',
        ]);
        $listing = Listing::find($request->listing_id);
        if(!$listing){
            return response()->json(['error' => 'listing to book not found'],404);
        }
        $renter = $request->user()->id;
        if(!User::find($renter)){
            return response()->json(['error' => 'renter not found'],404);
        }

        $bookings = Booking::where('listing_id', $request->listing_id)->get();
        $outt = Carbon::parse($request->check_out);
        $inn = Carbon::parse($request->check_in);
        foreach ($bookings as $booking) {
            $booking_check_in = Carbon::parse($booking->check_in);
            $booking_check_out = Carbon::parse($booking->check_out);

            if ($inn->between($booking_check_in, $booking_check_out) ||
            $outt->between($booking_check_in, $booking_check_out) ||
                ($inn->lte($booking_check_in) && $outt->gte($booking_check_out))) {
                return response()->json(['error'=> 'dates for booking is not available'],400); // Объект недоступен для бронирования
            }
        }
        $price_per_day = Listing::find($request->listing_id)->price_per_day;

    // Расчет общей стоимости аренды
    $days_diff = Carbon::parse($request->check_out)->diffInDays(Carbon::parse($request->check_in));
    $calculated_total_price = $price_per_day * (1+$days_diff);

        $booking = Booking::create([
            'listing_id'=>$request->listing_id,
            'renter_id'=>$renter,
            'check_in'=>$request->check_in,
            'check_out'=>$request->check_out,
            'total_price'=>$calculated_total_price,
            'status'=>'Создано'
        ]);
        return response()->json($booking);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    //вывод информации о конкретном бронировании пользователя
    public function show(Request $request, $id)
    {
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
    public function update(Request $request, int $id)//обновление бронирования
    {
        $request->validate([
            'check_in'=>'sometimes|required|date_format:Y-m-d H:i:s',
            'check_out'=>'sometimes|required|date_format:Y-m-d H:i:s',
            'status'=>'sometimes|required|string'
        ]);
        $booking = Booking::find($id);
        if(!$booking){
            return response()->json(['error' => 'Booking to update not found'],404);
        }
        $user_id = $request->user()->id;

        if ($user_id !== $booking->renter_id){
            return response()->json(['error' => 'Only booking creator can change it'], 403);
        }
        
        if ($request->has('check_in') and $request->has('check_out')){
            $other_bookings = Booking::where('listing_id', $booking->listing_id)->where('id', '!=', $request->id)->get();
            $outt = Carbon::parse($request->check_out);
            $inn = Carbon::parse($request->check_in);
            foreach ($other_bookings as $other_booking) {
                $other_check_in = Carbon::parse($other_booking->check_in);
                $other_check_out = Carbon::parse($other_booking->check_out);
                if ($inn->between($other_check_in, $other_check_out) ||
                $outt->between($other_check_in, $other_check_out) ||
                    ($inn->lte($other_check_in) && $outt->gte($other_check_out))) {
                    return response()->json(['error' => 'The new dates conflict with an existing bookings.'], 400);
                }
            }
            $price_per_day = Listing::find($booking->listing_id)->price_per_day;
            $days_diff = Carbon::parse($request->check_out)->diffInDays(Carbon::parse($request->check_in));
            $calculated_total_price = $price_per_day * (1+$days_diff);

            $booking->check_in = $request->check_in;
            $booking->check_out = $request->check_out;
            $booking->total_price = $calculated_total_price;
        }

        if ($request->has('status')){
            $booking->status = $request->status;
        }
        $booking->save();
        return response()->json($booking);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)//удаление бронирования
    {
        $booking = Booking::find($request->id);
        $user_id = $request->user()->id;
        if($user_id!== $booking->renter_id){
            return response()->json(['error'=> 'Only creator of the booking can delete it'],400);
        }
        if(!$booking){
            return response()->json(['error'=> 'Booking not found'],404);
        }

        $booking->delete();
        return response()->json(['message' => 'Booking deleted']);
    }
}
