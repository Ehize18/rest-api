<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_id',
        'author_id',
        'rate',
        'text',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'booking_id' => 'integer',
        'author_id' => 'integer',
        'rate' => 'integer',
        'text' => 'string',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the booking that owns the review.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the author of the review.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
