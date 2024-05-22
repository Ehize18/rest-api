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
     * добавил в роутах в вывод всех бронирований параметром renter_id т.к.
     * если все правильно понимаю, без id пользователя найти его
     * бронирования нельзя, а реквеста нет -> вытащить по другому
     * ид-шник юзера не получится
     */
    public function index($renter_id)// вывод всех бронирований пользователя
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
    public function store(Request $request)//создание бронирования
    {
        $request->validate([
            'listing_id'=>'required|numeric',
            'renter_id'=>'required|numeric',
            'check_in'=>'required|date_format:Y-m-d H:i:s',
            'check_out'=>'required|date_format:Y-m-d H:i:s',
            'total_price'=>'required|numeric',
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
        if(!($renter==$request->renter_id)){
            return response()->json(['error' => 'Only current user can do the booking for himself'],400);
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

    // Проверка соответствия цены аренды и total_price
    if ($calculated_total_price != $request->total_price) {
        return response()->json(['error'=> 'total_price does not match the calculated rental price'],400);
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



    // public function show($id){
    // $booking = Booking::find($id);
    // if(!$booking){
    //         return response()->json(['error' => 'Booking not found'],404);
    //     }
    //     return response()->json($booking);
    // }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)//обновление бронирования
    {
        $request->validate([
            'listing_id'=>'required|numeric',
            'renter_id'=>'required|numeric',
            'check_in'=>'required|date_format:Y-m-d H:i:s',
            'check_out'=>'required|date_format:Y-m-d H:i:s',
            'total_price'=>'required|numeric',
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
        $price_per_day = Listing::find($request->listing_id)->price_per_day;
        $days_diff = Carbon::parse($request->check_out)->diffInDays(Carbon::parse($request->check_in));
    $calculated_total_price = $price_per_day * (1+$days_diff);

    // Проверка соответствия цены аренды и total_price
    if ($calculated_total_price != $request->total_price) {
        return response()->json(['error'=> 'total_price does not match the calculated rental price'],400);
    }
        $booking->check_in = $request->check_in;
        $booking->check_out = $request->check_out;
        $booking->total_price = $request->total_price;
        $booking->save();
        return response()->json($booking);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)//удаление бронирования
    {
        $booking = Booking::find($id);
        if(!$booking){
            return response()->json(['error'=> 'Booking not found'],404);
        }
        $booking->delete();
        return response()->json(['message' => 'Booking deleted']);
    }
}
