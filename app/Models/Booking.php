<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'listing_id',
        'renter_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the listing associated with the booking.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the renter associated with the booking.
     */
    public function renter()
    {
        return $this->belongsTo(User::class, 'renter_id');
    }
}
