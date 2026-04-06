<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    protected $fillable = ['booking_id', 'status', 'remarks', 'changed_by_type', 'changed_by_id'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
